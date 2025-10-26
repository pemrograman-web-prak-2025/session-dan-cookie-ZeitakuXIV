<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToiletSession extends Model
{
    protected $fillable = [
        'id',
        'toilet_id',
        'npc_name',
        'start_time',
        'end_time',
        'is_active',
        'service_type',
        'price',
    ];

    /**
     * Get the toilet that this session belongs to
     */
    public function toilet(): BelongsTo
    {
        return $this->belongsTo(Toilet::class);
    }
}
