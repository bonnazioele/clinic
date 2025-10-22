<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicStatusLog extends Model
{
	use HasFactory;

	protected $fillable = [
		'clinic_id',
		'action',
		'reason',
		'performed_by',
		'performed_at',
	];

	protected $casts = [
		'performed_at' => 'datetime',
	];

	public function clinic(): BelongsTo
	{
		return $this->belongsTo(Clinic::class);
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'performed_by');
	}
}
