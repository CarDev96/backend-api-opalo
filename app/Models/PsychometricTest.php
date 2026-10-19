<?php

namespace App\Models;

use App\Services\FileUploaderService;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PsychometricTest extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

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

    public function examineeTests(): HasMany
    {
        return $this->hasMany(ExamineePsychometricTest::class, 'psychometric_test_id');
    }
}
