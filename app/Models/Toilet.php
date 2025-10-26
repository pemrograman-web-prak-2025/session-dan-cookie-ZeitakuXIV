<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Toilet extends Model
{
    protected $fillable = [
        'id',
        'level',
        'pee_price',
        'poop_price',
        'bath_price',
        'is_clean',
        'user_id',
    ];

    /**
     * Get the user that owns the toilet
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all sessions for this toilet
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(ToiletSession::class);
    }

    /**
     * Get active sessions for this toilet
     */
    public function activeSessions(): HasMany
    {
        return $this->hasMany(ToiletSession::class)->where('is_active', true);
    }

    /**
     * Check if toilet is currently occupied
     */
    public function isOccupied(): bool
    {
        return $this->activeSessions()->exists();
    }
}
