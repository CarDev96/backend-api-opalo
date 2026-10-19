<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'requires_purchase_order' => 'boolean',
        'is_active'               => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function examinees(): HasMany
    {
        return $this->hasMany(Examinee::class);
    }

    /**
     * Relación con el documento fiscal (CSF) en S3
     */
    public function fiscalDocument(): HasOne
    {
        return $this->hasOne(ClientFiscalDocument::class, 'client_id')->latestOfMany();
    }
}
