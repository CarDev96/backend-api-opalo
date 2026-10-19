<?php

namespace App\Models;

use App\Services\FileUploaderService;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationOrder extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'vat'         => 'decimal:2',
        'total'       => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    protected $appends = ['receipt_url'];

    public function getReceiptUrlAttribute(): ?string
    {
        if (!$this->receipt_file_path) {
            return null;
        }

        return app(FileUploaderService::class)->getTemporaryUrl($this->receipt_file_path, 45);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function examinee(): BelongsTo
    {
        return $this->belongsTo(Examinee::class, 'examinee_id');
    }

    public function evaluationType(): BelongsTo
    {
        return $this->belongsTo(EvaluationType::class, 'evaluation_type_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

	public function paymentFiles(): HasMany
    {
		return $this->hasMany(ClientPaymentFile::class, 'evaluation_order_id');
    }

	public function latestPaymentFile(): HasOne
    {
        return $this->hasOne(ClientPaymentFile::class, 'evaluation_order_id')->latestOfMany();
    }
}
