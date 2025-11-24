<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Mail\ResetPasswordMail;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'position_id',
        'department_id',
        'bank_id',
        'clabe',
        'rfc',
        'account_number',
        'override_authorization',
        'override_authorizer_id',
        'travel_team',
        'treasury_team',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'override_authorization' => 'boolean',
            'travel_team' => 'boolean',
            'treasury_team' => 'boolean',
        ];
    }

    /**
     * Get the position that the user belongs to.
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the department that the user belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the bank that the user uses.
     */
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * Get the override authorizer for this user.
     */
    public function overrideAuthorizer()
    {
        return $this->belongsTo(User::class, 'override_authorizer_id');
    }

    /**
     * Get users that have this user as override authorizer.
     */
    public function authorizedUsers()
    {
        return $this->hasMany(User::class, 'override_authorizer_id');
    }

    /**
     * Get the authorizer for this user.
     * If override_authorization is true, use override_authorizer_id
     * Otherwise, use the department's default authorizer
     */
    public function getAuthorizerAttribute()
    {
        if ($this->override_authorization && $this->override_authorizer_id) {
            return $this->overrideAuthorizer;
        }

        return $this->department?->defaultAuthorizer;
    }

    /**
     * Mutator para RFC: Convierte a mayúsculas y limpia el formato
     */
    public function setRfcAttribute($value)
    {
        if ($value) {
            // Convertir a mayúsculas y remover espacios/guiones
            $this->attributes['rfc'] = strtoupper(preg_replace('/[\s\-]/', '', $value));
        } else {
            $this->attributes['rfc'] = null;
        }
    }

    /**
     * Mutator para CLABE: Limpia el formato y valida longitud
     */
    public function setClabeAttribute($value)
    {
        if ($value) {
            // Remover espacios, guiones y otros caracteres no numéricos
            $cleanClabe = preg_replace('/[^\d]/', '', $value);

            // Validar que tenga exactamente 18 dígitos
            if (strlen($cleanClabe) === 18) {
                $this->attributes['clabe'] = $cleanClabe;
            } else {
                throw new \InvalidArgumentException('La CLABE debe tener exactamente 18 dígitos.');
            }
        } else {
            $this->attributes['clabe'] = null;
        }
    }

    /**
     * Mutator para override_authorization: Si es false, limpia override_authorizer_id
     */
    public function setOverrideAuthorizationAttribute($value)
    {
        $this->attributes['override_authorization'] = (bool) $value;

        // Si se desactiva la autorización especial, limpiar el autorizador personalizado
        if (! $value) {
            $this->attributes['override_authorizer_id'] = null;
        }
    }

    /**
     * Scope para obtener solo usuarios del equipo de viajes
     */
    public function scopeTravelTeam($query)
    {
        return $query->where('travel_team', true);
    }

    /**
     * Scope para obtener usuarios regulares (no del equipo de viajes)
     */
    public function scopeRegularUsers($query)
    {
        return $query->where('travel_team', false)->where('treasury_team', false);
    }

    /**
     * Scope para obtener solo usuarios del equipo de tesorería
     */
    public function scopeTreasuryTeam($query)
    {
        return $query->where('treasury_team', true);
    }

    /**
     * Scope para obtener usuarios con acceso especial (equipo de viajes O autorización especial)
     */
    public function scopeWithSpecialAccess($query)
    {
        return $query->where(function ($query) {
            $query->where('travel_team', true)
                ->orWhere('treasury_team', true)
                ->orWhere('override_authorization', true);
        });
    }

    /**
     * Verifica si el usuario pertenece al equipo de viajes
     */
    public function isTravelTeamMember(): bool
    {
        return (bool) $this->travel_team;
    }

    /**
     * Verifica si el usuario pertenece al equipo de tesorería
     */
    public function isTreasuryTeamMember(): bool
    {
        return (bool) $this->treasury_team;
    }

    /**
     * Verifica si el usuario tiene acceso especial (equipo de viajes, tesorería o autorización especial)
     */
    public function hasSpecialAccess(): bool
    {
        return $this->travel_team || $this->treasury_team || $this->override_authorization;
    }

    /**
     * Accessor para RFC: Formatea el RFC para mostrar
     */
    public function getRfcAttribute($value)
    {
        if ($value && strlen($value) === 13) {
            // Formato: AAAA000000AAA -> AAAA-000000-AAA
            return substr($value, 0, 4).'-'.substr($value, 4, 6).'-'.substr($value, 10, 3);
        }

        return $value;
    }

    /**
     * Accessor para CLABE: Solo formatea en vistas, no en formularios
     */
    public function getClabeFormattedAttribute()
    {
        $value = $this->attributes['clabe'] ?? null;
        if ($value && strlen($value) === 18) {
            // Formato: 000000000000000000 -> 000-000-00000000000-0
            return substr($value, 0, 3).'-'.substr($value, 3, 3).'-'.substr($value, 6, 11).'-'.substr($value, 17, 1);
        }

        return $value;
    }

    /**
     * Send the password reset notification using custom notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
