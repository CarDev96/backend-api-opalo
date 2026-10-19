<?php

namespace App\Models;

use App\Services\FileUploaderService;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientFiscalDocument extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $appends = ['file_url'];

    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return app(FileUploaderService::class)->getTemporaryUrl($this->file_path, 60);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
