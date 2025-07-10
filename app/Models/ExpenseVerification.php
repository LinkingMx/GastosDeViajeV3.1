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
    ];

    protected $casts = [
        'uuid' => 'string',
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
}
