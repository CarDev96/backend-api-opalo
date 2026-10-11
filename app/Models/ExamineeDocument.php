<?php

namespace App\Models;

use App\Services\FileUploaderService;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamineeDocument extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $appends = ['file_url'];

    /**
     * Resuelve la URL temporal firmada delegando en FileUploaderService
     */
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return app(FileUploaderService::class)->getTemporaryUrl($this->file_path, 45);
    }

    public function examinee(): BelongsTo
    {
        return $this->belongsTo(Examinee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
