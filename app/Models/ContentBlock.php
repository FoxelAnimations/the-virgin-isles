<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentBlock extends Model
{
    protected $fillable = [
        'pre_title',
        'title',
        'text',
        'media_type',
        'image_path',
        'video_path',
        'youtube_url',
        'button_label',
        'button_url',
        'button_color',
        'button_new_tab',
        'separator_color',
        'is_active',
        'placement',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'button_new_tab' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeForHome($query)
    {
        return $query->whereIn('placement', ['home', 'both']);
    }

    public function scopeForBlog($query)
    {
        return $query->whereIn('placement', ['blog', 'both']);
    }

    public function hasMedia(): bool
    {
        return $this->media_type !== null;
    }

    public function hasButton(): bool
    {
        return !empty($this->button_label) && !empty($this->button_url);
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if ($this->media_type !== 'youtube' || empty($this->youtube_url)) {
            return null;
        }

        $url = $this->youtube_url;

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]+)/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        if (str_contains($url, 'youtube.com/embed/')) {
            return $url;
        }

        return $url;
    }

    public function getEffectiveButtonColorAttribute(): string
    {
        return $this->button_color ?: '#E7FF57';
    }

    public function getEffectiveSeparatorColorAttribute(): string
    {
        return $this->separator_color ?: '#E7FF57';
    }
}
