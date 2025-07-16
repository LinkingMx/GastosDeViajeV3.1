<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ExpenseVerification extends Model
{
    protected $fillable = [
        'uuid',
        'travel_request_id',
        'created_by',
        'status',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'approved_by',
        'approval_notes',
        'reimbursement_status',
        'reimbursement_made',
        'reimbursement_made_at',
        'reimbursement_made_by',
        'reimbursement_amount',
        'reimbursement_notes',
        'reimbursement_attachments',
    ];

    protected $casts = [
        'uuid' => 'string',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'reimbursement_made_at' => 'datetime',
        'reimbursement_attachments' => 'array',
    ];

    /**
     * Boot method to auto-generate UUID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            // Si no se especifica created_by, usar el usuario autenticado
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }

    /**
     * Relación con la solicitud de viaje
     */
    public function travelRequest(): BelongsTo
    {
        return $this->belongsTo(TravelRequest::class);
    }

    /**
     * Relación con el usuario que creó la comprobación
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con el usuario que aprobó la comprobación
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relación con el usuario que realizó el reembolso
     */
    public function reimbursementMadeBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reimbursement_made_by');
    }

    /**
     * Relación con los comprobantes de gastos
     */
    public function receipts(): HasMany
    {
        return $this->hasMany(ExpenseReceipt::class);
    }

    /**
     * Relación con los comprobantes no deducibles
     */
    public function nonDeductibleReceipts(): HasMany
    {
        return $this->hasMany(ExpenseReceipt::class)->where('receipt_type', 'non_deductible');
    }

    /**
     * Relación con los comprobantes fiscales
     */
    public function fiscalReceipts(): HasMany
    {
        return $this->hasMany(ExpenseReceipt::class)->where('receipt_type', 'fiscal');
    }

    /**
     * Accessor para obtener el folio formateado
     */
    public function getFolioAttribute(): string
    {
        return 'COMP-'.strtoupper(substr($this->uuid, 0, 8));
    }

    /**
     * Accessor para obtener información del solicitante a través de la solicitud de viaje
     */
    public function getRequestorAttribute()
    {
        return $this->travelRequest?->user;
    }

    /**
     * Scope para filtrar por solicitud de viaje
     */
    public function scopeForTravelRequest($query, $travelRequestId)
    {
        return $query->where('travel_request_id', $travelRequestId);
    }

    /**
     * Scope para filtrar por usuario creador
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Obtener gastos pendientes por concepto (desde custom_expenses y per_diems)
     */
    public function getPendingExpensesByCategory(): array
    {
        if (!$this->travelRequest) {
            return [];
        }

        $pendingExpenses = [];
        
        // 1. Obtener gastos personalizados (custom_expenses_data)
        $customExpenses = $this->travelRequest->custom_expenses_data ?? [];
        foreach ($customExpenses as $expense) {
            if (!empty($expense['amount']) && !empty($expense['concept'])) {
                $conceptName = trim($expense['concept']);
                $pendingExpenses[$conceptName] = [
                    'name' => $conceptName,
                    'amount' => (float) $expense['amount'],
                    'description' => $expense['justification'] ?? '',
                    'source' => 'custom_expense',
                ];
            }
        }
        
        // 2. Obtener viáticos habilitados (per_diem_data)
        $perDiemData = $this->travelRequest->per_diem_data ?? [];
        if (!empty($perDiemData)) {
            $user = $this->travelRequest->user;
            $requestType = $this->travelRequest->request_type;
            
            if ($user && $user->position_id && $requestType) {
                $departureDate = $this->travelRequest->departure_date;
                $returnDate = $this->travelRequest->return_date;
                
                if ($departureDate && $returnDate) {
                    $totalDays = max(1, $departureDate->diffInDays($returnDate) + 1);
                    
                    $perDiems = \App\Models\PerDiem::with(['detail.concept'])
                        ->where('position_id', $user->position_id)
                        ->where('scope', $requestType)
                        ->get();
                        
                    foreach ($perDiems as $perDiem) {
                        $isEnabled = isset($perDiemData[$perDiem->id]) && 
                                   ($perDiemData[$perDiem->id]['enabled'] ?? false);
                                   
                        if ($isEnabled && $perDiem->detail) {
                            $detailName = $perDiem->detail->name;
                            $amount = $totalDays * $perDiem->amount;
                            
                            $pendingExpenses[$detailName] = [
                                'name' => $detailName,
                                'amount' => $amount,
                                'description' => 'Viático estándar por ' . $totalDays . ' días',
                                'source' => 'per_diem',
                            ];
                        }
                    }
                }
            }
        }
        
        return $pendingExpenses;
    }

    /**
     * Obtener total comprobado por detalle de gasto
     */
    public function getProvenExpensesByCategory(): array
    {
        $provenByDetail = [];
        
        foreach ($this->receipts as $receipt) {
            if ($receipt->expense_detail_id && $receipt->expenseDetail) {
                $detailName = $receipt->expenseDetail->name;
                if (!isset($provenByDetail[$detailName])) {
                    $provenByDetail[$detailName] = 0;
                }
                $provenByDetail[$detailName] += $receipt->applied_amount ?? $receipt->total_amount;
            }
        }
        
        return $provenByDetail;
    }

    /**
     * Obtener resumen de comprobación de gastos
     */
    public function getExpenseVerificationSummary(): array
    {
        $pending = $this->getPendingExpensesByCategory();
        $proven = $this->getProvenExpensesByCategory();
        
        $summary = [];
        
        // Agregar gastos pendientes
        foreach ($pending as $category => $data) {
            $summary[$category] = [
                'name' => $data['name'],
                'pending' => $data['amount'],
                'proven' => $proven[$category] ?? 0,
                'remaining' => $data['amount'] - ($proven[$category] ?? 0),
            ];
        }
        
        // Agregar gastos comprobados que no están en pendientes
        foreach ($proven as $category => $amount) {
            if (!isset($summary[$category])) {
                $summary[$category] = [
                    'name' => ucfirst(str_replace('_', ' ', $category)),
                    'pending' => 0,
                    'proven' => $amount,
                    'remaining' => -$amount, // Negativo indica exceso
                ];
            }
        }
        
        return $summary;
    }

    /**
     * Get the status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Borrador',
            'pending' => 'Pendiente de Autorización',
            'approved' => 'Autorizada',
            'rejected' => 'Rechazada',
            'revision' => 'En Revisión',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get the status badge class for UI display.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            'revision' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        };
    }

    /**
     * Check if the verification can be edited.
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'revision']);
    }

    /**
     * Check if the verification can be submitted for authorization.
     */
    public function canBeSubmitted(): bool
    {
        return in_array($this->status, ['draft', 'revision']);
    }

    /**
     * Check if the verification can be authorized by travel team.
     */
    public function canBeAuthorizedBy(User $user): bool
    {
        return $this->status === 'pending' && $user->isTravelTeamMember();
    }

    /**
     * Check if the verification can be put back in revision by its owner.
     */
    public function canBeRevisedBy(User $user): bool
    {
        return $this->status === 'rejected' && $this->created_by === $user->id;
    }

    /**
     * Submit the verification for authorization.
     */
    public function submitForAuthorization(): void
    {
        $this->update([
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
    }


    /**
     * Reject the verification with notes.
     */
    public function reject(User $approver, string $notes): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'approved_by' => $approver->id,
            'approval_notes' => $notes,
        ]);
    }

    /**
     * Put the verification back in revision for editing.
     */
    public function putInRevision(): void
    {
        $this->update([
            'status' => 'revision',
            'rejected_at' => null,
            'approved_by' => null,
            'approval_notes' => null,
        ]);
    }

    /**
     * Get total verified amount from receipts.
     */
    public function getTotalVerifiedAmount(): float
    {
        return $this->receipts->sum(function ($receipt) {
            return $receipt->applied_amount ?? $receipt->total_amount;
        });
    }

    /**
     * Get advance deposit amount from travel request.
     */
    public function getAdvanceDepositAmount(): float
    {
        return $this->travelRequest?->advance_deposit_amount ?? 0;
    }

    /**
     * Check if reimbursement is needed (verified amount > advance deposit).
     */
    public function needsReimbursement(): bool
    {
        return $this->getTotalVerifiedAmount() > $this->getAdvanceDepositAmount();
    }

    /**
     * Calculate reimbursement amount needed.
     */
    public function getReimbursementAmountNeeded(): float
    {
        $difference = $this->getTotalVerifiedAmount() - $this->getAdvanceDepositAmount();
        return max(0, $difference);
    }

    /**
     * Check if verification can be marked as needing reimbursement.
     */
    public function canBeMarkedForReimbursement(): bool
    {
        return $this->status === 'approved' && 
               $this->needsReimbursement() && 
               !$this->reimbursement_status;
    }

    /**
     * Mark verification as needing reimbursement.
     */
    public function markForReimbursement(): void
    {
        if ($this->canBeMarkedForReimbursement()) {
            $this->update([
                'reimbursement_status' => 'pending_reimbursement',
            ]);
        }
    }

    /**
     * Check if verification can be reimbursed by treasury team.
     */
    public function canBeReimbursedBy(User $user): bool
    {
        return $this->reimbursement_status === 'pending_reimbursement' && 
               $user->isTreasuryTeamMember();
    }

    /**
     * Mark reimbursement as made by treasury team.
     */
    public function markReimbursementMade(User $user, float $amount, ?string $notes = null, ?array $attachments = null): void
    {
        $this->update([
            'reimbursement_made' => true,
            'reimbursement_made_at' => now(),
            'reimbursement_made_by' => $user->id,
            'reimbursement_amount' => $amount,
            'reimbursement_notes' => $notes,
            'reimbursement_attachments' => $attachments,
            'reimbursement_status' => 'reimbursed',
            'status' => 'closed', // Close the verification definitively
        ]);
    }

    /**
     * Get reimbursement status display name.
     */
    public function getReimbursementStatusDisplayAttribute(): ?string
    {
        return match ($this->reimbursement_status) {
            'pending_reimbursement' => 'En Reembolso',
            'reimbursed' => 'Reembolsada',
            default => null,
        };
    }

    /**
     * Get combined status display (authorization + reimbursement).
     */
    public function getCombinedStatusDisplayAttribute(): string
    {
        if ($this->status === 'closed') {
            return 'Cerrada';
        }

        if ($this->reimbursement_status) {
            return $this->reimbursement_status_display ?? $this->status_display;
        }

        return $this->status_display;
    }

    /**
     * Override the approval method to check for reimbursement.
     */
    public function approve(User $approver, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approver->id,
            'approval_notes' => $notes,
        ]);

        // Check if reimbursement is needed after approval
        if ($this->needsReimbursement()) {
            $this->markForReimbursement();
        }
    }
}
