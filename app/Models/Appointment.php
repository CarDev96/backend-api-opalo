<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

	public function examinee(): BelongsTo
	{
		return $this->belongsTo(Examinee::class, 'examinee_id');
	}

    public function psychologist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'psychologist_id');
    }

    public function evaluationType(): BelongsTo
    {
        return $this->belongsTo(EvaluationType::class);
    }
}
