<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Badge extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'image_path',
        'description',
        'popup_text_first',
        'popup_text_repeat',
        'type_id',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(BadgeType::class, 'type_id');
    }

    public function beacons(): BelongsToMany
    {
        return $this->belongsToMany(Beacon::class, 'badge_beacon');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'badge_user')
            ->withPivot('count', 'collected_at', 'updated_at')
            ->withCasts(['collected_at' => 'datetime', 'updated_at' => 'datetime']);
    }

    public function toPopupArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image' => $this->image_path ? Storage::url($this->image_path) : null,
            'popup_text' => $this->popup_text_first,
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
