<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'student_id', 'term_id',
        'total_amount', 'amount_paid', 'balance', 'status', 'due_date',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    /**
     * Format amount in GHS.
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'GHS ' . number_format($this->total_amount, 2);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return 'GHS ' . number_format($this->balance, 2);
    }
}
