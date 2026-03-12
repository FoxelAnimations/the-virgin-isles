<?php

namespace App\Mail;

use App\Models\Character;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChatOfflineNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $visitorMessage,
        public Character $character,
        public string $visitorIp,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nieuw chatbericht — ' . $this->character->full_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.chat-offline',
        );
    }
}
