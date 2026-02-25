<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Episode extends Model
{
    protected $fillable = [
        'title',
        'description',
        'source_type',
        'video_path',
        'youtube_url',
        'thumbnail_path',
        'instagram_url',
        'youtube_link',
        'tiktok_url',
        'twitter_url',
        'sort_order',
    ];

    public function characters()
    {
        return $this->belongsToMany(Character::class);
    }

    public function isYoutube(): bool
    {
        return $this->source_type === 'youtube';
    }

    public function youtubeEmbedUrl(): ?string
    {
        if (! $this->youtube_url) {
            return null;
        }

        $videoId = $this->extractYoutubeId();
        return $videoId ? 'https://www.youtube.com/embed/' . $videoId . '?autoplay=1' : null;
    }

    public function youtubeThumbnailUrl(): ?string
    {
        $videoId = $this->extractYoutubeId();
        return $videoId ? 'https://img.youtube.com/vi/' . $videoId . '/hqdefault.jpg' : null;
    }

    protected function extractYoutubeId(): ?string
    {
        if (! $this->youtube_url) {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/|live\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $this->youtube_url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function videoUrl(): ?string
    {
        return $this->video_path ? Storage::url($this->video_path) : null;
    }

    public function thumbnailUrl(): ?string
    {
        if ($this->thumbnail_path) {
            return Storage::url($this->thumbnail_path);
        }

        if ($this->isYoutube()) {
            return $this->youtubeThumbnailUrl();
        }

        return null;
    }
}
