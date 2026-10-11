<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsychologistSchedule extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'day_of_week'           => 'integer',
        'slot_duration_minutes' => 'integer',
        'is_active'             => 'boolean',
    ];

    public function psychologist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
