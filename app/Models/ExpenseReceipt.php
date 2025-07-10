<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseReceipt extends Model
{
    protected $fillable = [
        'expense_verification_id',
        'receipt_type',
        'total_amount',
        'currency',
        'supplier_name',
        'supplier_rfc',
        'receipt_date',
        'xml_file_path',
        'pdf_file_path',
        'photo_file_path',
        'cfdi_uuid',
        'status',
        'notes',
        'expense_detail_id',
        'expense_category',
        'applied_amount',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'total_amount' => 'decimal:2',
        'applied_amount' => 'decimal:2',
    ];

    public function expenseVerification(): BelongsTo
    {
        return $this->belongsTo(ExpenseVerification::class);
    }

    public function expenseConcept(): BelongsTo
    {
        return $this->belongsTo(ExpenseConcept::class);
    }

    public function expenseDetail(): BelongsTo
    {
        return $this->belongsTo(ExpenseDetail::class, 'expense_detail_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->total_amount, 2);
    }

    public function isNonDeductible(): bool
    {
        return $this->receipt_type === 'non_deductible';
    }

    public function isFiscal(): bool
    {
        return $this->receipt_type === 'fiscal';
    }
}
