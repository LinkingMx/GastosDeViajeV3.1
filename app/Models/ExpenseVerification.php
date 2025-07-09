<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
}
