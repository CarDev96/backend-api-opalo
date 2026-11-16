<?php

namespace App\Models;

use App\Services\FileUploaderService;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PsychometricTestFile extends Model
{
    use HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $appends = ['url'];

    public function getUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return app(FileUploaderService::class)->getTemporaryUrl($this->file_path, 45);
    }

    public function psychometricTest(): BelongsTo
    {
        return $this->belongsTo(PsychometricTest::class, 'psychometric_test_id');
    }
}
