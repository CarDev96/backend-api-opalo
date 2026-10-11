<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Examinee extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [];

    /**
     * Relación con la empresa cliente
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Relación: Un evaluado tiene múltiples documentos/fotografías
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ExamineeDocument::class, 'examinee_id');
    }

    /**
     * Relación: Un evaluado tiene múltiples referencias personales
     */
    public function references(): HasMany
    {
        return $this->hasMany(ExamineeReference::class, 'examinee_id');
    }
}
