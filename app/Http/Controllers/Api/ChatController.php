<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatBlock;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use OpenAI\Laravel\Facades\OpenAI;

class ChatController extends Controller
{
    /** Max visitor messages per day (across all conversations). */
    private const VISITOR_DAILY_LIMIT = 100;

    /** Max AI responses generated globally per day. */
    private const GLOBAL_AI_DAILY_LIMIT = 1000;

    public function characters(): JsonResponse
    {
        $settings = SiteSetting::first();

        if (!$settings?->chat_enabled) {
            return response()->json(['characters' => [], 'default_character_id' => null]);
        }

        $characters = Character::where('chat_enabled', true)
            ->orderBy('sort_order')
            ->get(['id', 'first_name', 'last_name', 'nick_name', 'profile_image_path', 'profile_photo_path', 'chat_mode', 'chat_online']);

        return response()->json([
            'characters' => $characters->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->full_name,
                'first_name' => $c->first_name,
                'profile_image_url' => ($c->profile_photo_path ?? $c->profile_image_path)
                    ? asset('storage/' . ($c->profile_photo_path ?? $c->profile_image_path))
                    : null,
                'chat_mode' => $c->chat_mode,
                'chat_online' => (bool) $c->chat_online,
            ]),
            'default_character_id' => $settings->default_chat_character_id,
            'notification_sound_url' => $settings->chat_notification_sound
                ? asset('storage/' . $settings->chat_notification_sound)
                : null,
            'blocked_sound_url' => $settings->chat_blocked_sound
                ? asset('storage/' . $settings->chat_blocked_sound)
                : null,
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        $settings = SiteSetting::first();

        if (!$settings?->chat_enabled) {
            return response()->json(['error' => 'Chat is momenteel uitgeschakeld.'], 403);
        }

        $validated = $request->validate([
            'character_id' => ['required', 'integer', 'exists:characters,id'],
            'message' => ['required', 'string', 'max:1000'],
            'visitor_uuid' => ['required', 'uuid'],
        ]);

        // Check if visitor is blocked
        $visitorIp = $request->ip();
        if (ChatBlock::isBlocked($visitorIp, $validated['visitor_uuid'])) {
            // Record the blocked attempt so admin sees a ping
            ChatConversation::where('visitor_uuid', $validated['visitor_uuid'])
                ->where('character_id', $validated['character_id'])
                ->where('status', 'open')
                ->update(['blocked_attempt_at' => now()]);

            return response()->json(['error' => 'Je bent geblokkeerd voor de chat.'], 403);
        }

        // Cooldown: 3 seconds between messages per visitor
        $cooldownKey = 'chat_cooldown:' . $validated['visitor_uuid'];
        if (Cache::has($cooldownKey)) {
            return response()->json([
                'error' => 'Wacht even voordat je een nieuw bericht stuurt.',
            ], 429);
        }
        Cache::put($cooldownKey, true, 3);

        // Duplicate message prevention (same visitor + same text within 30s)
        $dupeKey = 'chat_dupe:' . $validated['visitor_uuid'] . ':' . md5($validated['message']);
        if (Cache::has($dupeKey)) {
            return response()->json([
                'error' => 'Dit bericht is al verstuurd.',
            ], 429);
        }
        Cache::put($dupeKey, true, 30);

        $character = Character::findOrFail($validated['character_id']);

        if (!$character->chat_enabled) {
            return response()->json(['error' => 'Dit personage is niet beschikbaar voor chat.'], 403);
        }

        // Per-visitor daily message limit
        $todayCount = ChatMessage::where('sender', 'visitor')
            ->where('created_at', '>=', now()->startOfDay())
            ->whereHas('conversation', fn ($q) => $q->where('visitor_uuid', $validated['visitor_uuid']))
            ->count();

        if ($todayCount >= self::VISITOR_DAILY_LIMIT) {
            return response()->json([
                'error' => 'Je hebt het maximale aantal berichten voor vandaag bereikt. Probeer het morgen opnieuw.',
            ], 429);
        }

        // Find or create conversation
        $conversation = ChatConversation::firstOrCreate(
            [
                'visitor_uuid' => $validated['visitor_uuid'],
                'character_id' => $character->id,
                'status' => 'open',
            ],
            [
                'last_message_at' => now(),
                'visitor_ip' => $visitorIp,
            ]
        );

        // Keep IP up to date
        if ($conversation->visitor_ip !== $visitorIp) {
            $conversation->update(['visitor_ip' => $visitorIp]);
        }

        // Store visitor message
        $conversation->messages()->create([
            'sender' => 'visitor',
            'content' => $validated['message'],
            'is_ai' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'unread_count' => $conversation->unread_count + 1,
        ]);

        // AI mode: auto-respond via OpenAI
        if ($character->isAiChat()) {
            return $this->handleAiResponse($conversation, $character);
        }

        // Manual mode: message stored, admin will respond later
        return response()->json([
            'conversation_id' => $conversation->id,
            'mode' => 'manual',
            'message' => null,
        ]);
    }

    public function poll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => ['required', 'integer', 'exists:chat_conversations,id'],
            'visitor_uuid' => ['required', 'uuid'],
            'after_id' => ['nullable', 'integer'],
        ]);

        $conversation = ChatConversation::where('id', $validated['conversation_id'])
            ->where('visitor_uuid', $validated['visitor_uuid'])
            ->firstOrFail();

        $query = $conversation->messages()
            ->where('sender', 'character');

        if (!empty($validated['after_id'])) {
            $query->where('id', '>', $validated['after_id']);
        }

        $messages = $query->get(['id', 'content', 'created_at']);

        $isBlocked = ChatBlock::isBlocked($request->ip(), $validated['visitor_uuid']);

        return response()->json([
            'messages' => $messages,
            'status' => $conversation->status,
            'blocked' => $isBlocked,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'visitor_uuid' => ['required', 'uuid'],
            'character_id' => ['required', 'integer', 'exists:characters,id'],
            'before_id' => ['nullable', 'integer'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $conversation = ChatConversation::where('visitor_uuid', $validated['visitor_uuid'])
            ->where('character_id', $validated['character_id'])
            ->where('status', 'open')
            ->first();

        if (!$conversation) {
            return response()->json(['conversation_id' => null, 'messages' => [], 'has_more' => false]);
        }

        $limit = $validated['limit'] ?? 30;

        $query = $conversation->messages();

        if (!empty($validated['before_id'])) {
            $query->where('id', '<', $validated['before_id']);
        }

        $totalCount = $conversation->messages()->count();
        $messages = $query->reorder()
            ->latest('id')
            ->take($limit)
            ->get(['id', 'sender', 'content', 'created_at'])
            ->sortBy('id')
            ->values()
            ->map(fn ($m) => [
                'id' => $m->id,
                'role' => $m->sender === 'visitor' ? 'user' : 'assistant',
                'content' => $m->content,
            ]);

        $loadedSoFar = !empty($validated['before_id'])
            ? $conversation->messages()->where('id', '>=', $messages->first()['id'] ?? 0)->count()
            : $messages->count();

        return response()->json([
            'conversation_id' => $conversation->id,
            'messages' => $messages,
            'has_more' => $loadedSoFar < $totalCount,
        ]);
    }

    protected function handleAiResponse(ChatConversation $conversation, Character $character): JsonResponse
    {
        // Global daily AI call cap
        $aiCallsToday = ChatMessage::where('is_ai', true)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        if ($aiCallsToday >= self::GLOBAL_AI_DAILY_LIMIT) {
            return response()->json([
                'conversation_id' => $conversation->id,
                'mode' => 'ai',
                'message' => 'Het is erg druk op dit moment. Probeer het later opnieuw.',
                'message_id' => null,
            ]);
        }

        $recentMessages = $conversation->messages()
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->reverse()
            ->values();

        $openAiMessages = [
            ['role' => 'system', 'content' => $character->buildSystemPrompt()],
        ];

        foreach ($recentMessages as $msg) {
            $openAiMessages[] = [
                'role' => $msg->sender === 'visitor' ? 'user' : 'assistant',
                'content' => $msg->content,
            ];
        }

        try {
            $response = OpenAI::chat()->create([
                'model' => config('openai.chat_model', 'gpt-4o-mini'),
                'messages' => $openAiMessages,
                'max_tokens' => 500,
                'temperature' => 0.8,
            ]);

            $aiContent = $response->choices[0]->message->content;

            $aiMessage = $conversation->messages()->create([
                'sender' => 'character',
                'content' => $aiContent,
                'is_ai' => true,
            ]);

            $conversation->update(['last_message_at' => now()]);

            return response()->json([
                'conversation_id' => $conversation->id,
                'mode' => 'ai',
                'message' => $aiContent,
                'message_id' => $aiMessage->id,
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'error' => 'Er ging iets mis. Probeer het opnieuw.',
            ], 500);
        }
    }
}
