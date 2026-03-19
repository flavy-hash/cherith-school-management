<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'student_id', 'term', 'year', 'amount', 'balance',
        'payment_method', 'transaction_id', 'notes',
        'is_verified', 'verified_at', 'verified_by', 'payment_date',
        'tra_receipt_number', 'tra_verification_code', 'tra_qr_code',
        'tra_receipt_payload', 'tra_receipt_synced_at'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'payment_date' => 'date',
        'verified_at' => 'datetime',
        'tra_receipt_payload' => 'array',
        'tra_receipt_synced_at' => 'datetime',
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
