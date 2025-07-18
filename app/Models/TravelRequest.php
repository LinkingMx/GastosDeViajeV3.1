<?php

namespace App\Models;

use App\Events\TravelRequestCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TravelRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'authorizer_id',
        'branch_id',
        'origin_country_id',
        'origin_city',
        'destination_country_id',
        'destination_city',
        'departure_date',
        'return_date',
        'status',
        'request_type',
        'notes',
        'additional_services',
        'per_diem_data',
        'custom_expenses_data',
        'submitted_at',
        'authorized_at',
        'rejected_at',
        'advance_deposit_made',
        'advance_deposit_made_at',
        'advance_deposit_made_by',
        'advance_deposit_notes',
        'advance_deposit_amount',
    ];

    /**
     * The model's boot method.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        static::created(function ($model) {
            // Disparar evento solo cuando se crea una nueva solicitud
            TravelRequestCreated::dispatch($model);
        });
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'additional_services' => 'array',
        'per_diem_data' => 'array',
        'custom_expenses_data' => 'array',
        'departure_date' => 'date',
        'return_date' => 'date',
        'submitted_at' => 'datetime',
        'authorized_at' => 'datetime',
        'rejected_at' => 'datetime',
        'advance_deposit_made' => 'boolean',
        'advance_deposit_made_at' => 'datetime',
        'advance_deposit_amount' => 'decimal:2',
    ];

    /**
     * Get the user who created the travel request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who authorized the travel request.
     */
    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorizer_id');
    }

    /**
     * Get the user who made the advance deposit.
     */
    public function advanceDepositMadeByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advance_deposit_made_by');
    }

    /**
     * Get the branch for the travel request.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the origin country for the travel request.
     */
    public function originCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'origin_country_id');
    }

    /**
     * Get the destination country for the travel request.
     */
    public function destinationCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'destination_country_id');
    }

    /**
     * Get the short folio from UUID.
     */
    public function getFolioAttribute(): string
    {
        return $this->uuid ? strtoupper(substr($this->uuid, 0, 8)) : 'PENDING';
    }

    /**
     * Get the full folio display format.
     */
    public function getFullFolioAttribute(): string
    {
        return $this->uuid ? "FOLIO-{$this->folio}" : 'FOLIO-PENDING';
    }

    /**
     * Get the comments for this travel request.
     */
    public function comments()
    {
        return $this->hasMany(TravelRequestComment::class)->orderBy('created_at');
    }

    /**
     * Get the attachments for this travel request.
     */
    public function attachments()
    {
        return $this->hasMany(TravelRequestAttachment::class)->orderBy('created_at');
    }

    /**
     * Get the expense verifications for this travel request.
     */
    public function expenseVerifications()
    {
        return $this->hasMany(ExpenseVerification::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the actual authorizer for this request based on user settings.
     */
    public function getActualAuthorizerAttribute()
    {
        // 1. Si el usuario tiene override de autorización, usar el autorizador específico
        if ($this->user->override_authorization && $this->user->override_authorizer_id) {
            return User::find($this->user->override_authorizer_id);
        }

        // 2. Si no, usar el autorizador del departamento
        return $this->user->department?->authorizer ?? null;
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
            'travel_review' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            'travel_approved' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
            'travel_rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            'pending_verification' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        };
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
            'travel_review' => 'En Revisión de Viajes',
            'travel_approved' => 'Aprobada Final',
            'travel_rejected' => 'Rechazada por Viajes',
            'pending_verification' => 'Por Comprobar',
            default => ucfirst($this->status),
        };
    }

    /**
     * Check if the request can be edited.
     * Draft: Nueva solicitud que puede ser editada
     * Revision: Solicitud rechazada que el usuario puso en revisión para modificar
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'revision']);
    }

    /**
     * Check if the request can be submitted for authorization.
     * Solo se puede enviar desde Draft o Revision (después de modificar)
     */
    public function canBeSubmitted(): bool
    {
        return in_array($this->status, ['draft', 'revision']);
    }

    /**
     * Check if the request can be authorized by a specific user.
     */
    public function canBeAuthorizedBy(User $user): bool
    {
        return $this->status === 'pending' &&
               $this->actual_authorizer &&
               $this->actual_authorizer->id === $user->id;
    }

    /**
     * Check if the request can be put back in revision by its owner.
     */
    public function canBeRevisedBy(User $user): bool
    {
        return $this->status === 'rejected' &&
               $this->user_id === $user->id;
    }

    /**
     * Submit the request for authorization.
     */
    public function submitForAuthorization(): void
    {
        $this->update([
            'status' => 'pending',
            'submitted_at' => now(),
            'authorizer_id' => $this->actual_authorizer?->id,
        ]);

        // Crear comentario de envío
        $this->comments()->create([
            'user_id' => auth()->id(),
            'comment' => 'Solicitud enviada para autorización.',
            'type' => 'submission',
        ]);

        // Enviar correo al autorizador
        if ($this->actual_authorizer) {
            \Illuminate\Support\Facades\Mail::to($this->actual_authorizer->email)
                ->send(new \App\Mail\TravelRequestPendingAuthorizationMail($this));

            // Crear notificación de campanita al autorizador usando Laravel notifications
            $this->actual_authorizer->notify(new \App\Notifications\TravelRequestNotification(
                '⚠️ Solicitud Pendiente de Autorización',
                "La solicitud de viaje {$this->folio} de {$this->user->name} requiere tu autorización.",
                $this
            ));
        }
    }

    /**
     * Approve the request.
     */
    public function approve(?string $comment = null): void
    {
        $this->update([
            'status' => 'approved',
            'authorized_at' => now(),
        ]);

        // Crear comentario de aprobación
        $this->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $comment ?: 'Solicitud aprobada.',
            'type' => 'approval',
        ]);

        // Enviar correo al solicitante notificando la autorización
        \Illuminate\Support\Facades\Mail::to($this->user->email)
            ->send(new \App\Mail\TravelRequestAuthorizedMail($this));

        // Crear notificación de campanita al solicitante
        $this->user->notify(new \App\Notifications\TravelRequestNotification(
            '✅ Solicitud Autorizada',
            "Tu solicitud de viaje {$this->folio} ha sido autorizada por {$this->authorizer->name}.",
            $this
        ));

        // NUEVA LÓGICA: Pasar automáticamente a revisión de viajes
        $this->moveToTravelReview();
    }

    /**
     * Reject the request with a comment.
     */
    public function reject(string $comment): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        // Crear comentario de rechazo
        $this->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $comment,
            'type' => 'rejection',
        ]);
    }

    /**
     * Put the request back in revision for editing.
     * Permite al usuario editar una solicitud rechazada.
     */
    public function putInRevision(): void
    {
        $this->update([
            'status' => 'revision',
            'rejected_at' => null,
        ]);

        // Crear comentario de revisión
        $this->comments()->create([
            'user_id' => auth()->id(),
            'comment' => 'Solicitud puesta en revisión para realizar modificaciones y reenvío.',
            'type' => 'revision',
        ]);
    }

    // ===============================================
    // MÉTODOS PARA EQUIPO DE VIAJES
    // ===============================================

    /**
     * Verifica si la solicitud está en revisión del equipo de viajes
     */
    public function isInTravelReview(): bool
    {
        return $this->status === 'travel_review';
    }

    /**
     * Verifica si la solicitud fue aprobada por el equipo de viajes
     */
    public function isTravelApproved(): bool
    {
        return $this->status === 'travel_approved';
    }

    /**
     * Verifica si la solicitud fue rechazada por el equipo de viajes
     */
    public function isTravelRejected(): bool
    {
        return $this->status === 'travel_rejected';
    }

    /**
     * Verifica si el usuario puede revisar esta solicitud (debe ser del equipo de viajes)
     */
    public function canBeTravelReviewedBy(User $user): bool
    {
        return $this->status === 'travel_review' && $user->isTravelTeamMember();
    }

    /**
     * Verificar si un usuario puede subir archivos adjuntos
     * Solo miembros del equipo de viajes pueden subir archivos cuando está en "aprobada final"
     */
    public function canUploadAttachments(User $user): bool
    {
        return $this->status === 'travel_approved' && $user->isTravelTeamMember();
    }

    /**
     * Verificar si un usuario puede marcar el depósito de anticipo
     * Solo miembros del equipo de tesorería pueden hacerlo en solicitudes aprobadas
     */
    public function canMarkAdvanceDeposit(User $user): bool
    {
        return in_array($this->status, ['approved', 'travel_review', 'travel_approved'])
               && $user->isTreasuryTeamMember()
               && ! $this->advance_deposit_made;
    }

    /**
     * Marcar el depósito de anticipo como realizado
     */
    public function markAdvanceDepositMade(User $user, ?float $amount = null, ?string $notes = null): void
    {
        if (! $this->canMarkAdvanceDeposit($user)) {
            throw new \Exception('No tienes permisos para marcar el depósito de anticipo en esta solicitud.');
        }

        $this->update([
            'advance_deposit_made' => true,
            'advance_deposit_made_at' => now(),
            'advance_deposit_made_by' => $user->id,
            'advance_deposit_amount' => $amount,
            'advance_deposit_notes' => $notes,
            'status' => 'pending_verification', // Cambiar estado a "Por comprobar"
        ]);
        
        // Crear comentario automático del cambio de estado
        $this->comments()->create([
            'user_id' => $user->id,
            'comment' => 'Depósito de anticipo realizado. La solicitud está ahora pendiente de comprobación de gastos.',
            'type' => 'treasury_deposit',
        ]);

        // Enviar correo al solicitante notificando el depósito
        \Illuminate\Support\Facades\Mail::to($this->user->email)
            ->send(new \App\Mail\TravelRequestAdvanceDepositMail($this));

        // Crear notificación de campanita al solicitante
        $this->user->notify(new \App\Notifications\TravelRequestNotification(
            '💰 Anticipo Depositado',
            "El anticipo para tu solicitud de viaje {$this->folio} ha sido depositado" . ($amount ? " por $" . number_format($amount, 2) : '') . " por el equipo de tesorería.",
            $this
        ));
    }

    /**
     * Verificar si se puede desmarcar el depósito de anticipo
     */
    public function canUnmarkAdvanceDeposit(User $user): bool
    {
        return $this->advance_deposit_made
               && $user->isTreasuryTeamMember()
               && $this->advance_deposit_made_by === $user->id;
    }

    /**
     * Desmarcar el depósito de anticipo
     */
    public function unmarkAdvanceDeposit(User $user): void
    {
        if (! $this->canUnmarkAdvanceDeposit($user)) {
            throw new \Exception('No puedes desmarcar este depósito de anticipo.');
        }

        $this->update([
            'advance_deposit_made' => false,
            'advance_deposit_made_at' => null,
            'advance_deposit_made_by' => null,
            'advance_deposit_amount' => null,
            'advance_deposit_notes' => null,
            'status' => 'travel_approved', // Regresar a estado aprobado final
        ]);
        
        // Crear comentario del cambio
        $this->comments()->create([
            'user_id' => $user->id,
            'comment' => 'Depósito de anticipo desmarcado. La solicitud regresa al estado de aprobada final.',
            'type' => 'treasury_unmark',
        ]);
    }

    /**
     * Mueve la solicitud automáticamente a revisión del equipo de viajes
     * Se ejecuta cuando una solicitud es aprobada departamentalmente
     */
    public function moveToTravelReview(): void
    {
        $this->update([
            'status' => 'travel_review',
        ]);

        // Crear comentario automático
        $this->comments()->create([
            'user_id' => null, // Sistema
            'comment' => 'Solicitud enviada automáticamente a revisión del equipo de viajes.',
            'type' => 'system',
        ]);

        // Enviar correo a todos los miembros del equipo de viajes
        $travelTeamMembers = \App\Models\User::travelTeam()->get();
        foreach ($travelTeamMembers as $member) {
            \Illuminate\Support\Facades\Mail::to($member->email)
                ->send(new \App\Mail\TravelRequestPendingTravelReviewMail($this));

            // Crear notificación de campanita para cada miembro del equipo de viajes
            $member->notify(new \App\Notifications\TravelRequestNotification(
                '🔍 Nueva Solicitud para Revisión',
                "La solicitud de viaje {$this->folio} de {$this->user->name} requiere revisión del equipo de viajes.",
                $this
            ));
        }
    }

    /**
     * Aprueba la solicitud por parte del equipo de viajes
     */
    public function travelApprove(User $reviewer, ?string $comment = null): void
    {
        if (! $reviewer->isTravelTeamMember()) {
            throw new \Exception('Solo miembros del equipo de viajes pueden aprobar solicitudes.');
        }

        $this->update([
            'status' => 'travel_approved',
        ]);

        // Crear comentario de aprobación
        $this->comments()->create([
            'user_id' => $reviewer->id,
            'comment' => $comment ?? 'Solicitud aprobada por el equipo de viajes.',
            'type' => 'travel_approval',
        ]);

        // Enviar correo al solicitante
        \Illuminate\Support\Facades\Mail::to($this->user->email)
            ->send(new \App\Mail\TravelRequestTravelApprovedMail($this));

        // Crear notificación de campanita al solicitante
        $this->user->notify(new \App\Notifications\TravelRequestNotification(
            '🎉 Viaje Aprobado Final',
            "Tu solicitud de viaje {$this->folio} ha sido aprobada finalmente por el equipo de viajes.",
            $this
        ));
    }

    /**
     * Rechaza la solicitud por parte del equipo de viajes
     */
    public function travelReject(User $reviewer, string $reason): void
    {
        if (! $reviewer->isTravelTeamMember()) {
            throw new \Exception('Solo miembros del equipo de viajes pueden rechazar solicitudes.');
        }

        $this->update([
            'status' => 'travel_rejected',
        ]);

        // Crear comentario de rechazo
        $this->comments()->create([
            'user_id' => $reviewer->id,
            'comment' => $reason,
            'type' => 'travel_rejection',
        ]);
    }

    /**
     * Modifica los gastos especiales y aprueba la solicitud
     */
    public function travelEditAndApprove(User $reviewer, array $newCustomExpenses, string $comment): void
    {
        if (! $reviewer->isTravelTeamMember()) {
            throw new \Exception('Solo miembros del equipo de viajes pueden editar gastos.');
        }

        $this->update([
            'status' => 'travel_approved',
            'custom_expenses_data' => $newCustomExpenses,
        ]);

        // Crear comentario detallado
        $this->comments()->create([
            'user_id' => $reviewer->id,
            'comment' => $comment,
            'type' => 'travel_edit_approval',
        ]);
    }
}
