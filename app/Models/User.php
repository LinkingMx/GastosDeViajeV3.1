<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
}
