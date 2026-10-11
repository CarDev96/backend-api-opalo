<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ClientPaymentFile extends Model
{
    use HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $appends = ['file_url'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Genera la URL temporal firmada (30 min) desde el path plano almacenado
     */
    public function getFileUrlAttribute(): ?string
    {
        if (empty($this->path)) {
            return null;
        }

        if (Storage::disk('s3')->exists($this->path)) {
            return Storage::disk('s3')->temporaryUrl($this->path, now()->addMinutes(30));
        }

        return null;
    }
}
