<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationType extends Model
{
    use HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
