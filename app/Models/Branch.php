<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'ceco',
        'tax_id',
        'is_default',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Boot method for model events and validations.
     */
    protected static function boot()
    {
        parent::boot();

        // Validación antes de crear
        static::creating(function ($branch) {
            static::validateTaxId($branch);
            static::validateCeco($branch);
        });

        // Validación antes de actualizar
        static::updating(function ($branch) {
            static::validateTaxId($branch);
            static::validateCeco($branch);
        });

        // Ensure only one branch can be default
        static::saving(function ($branch) {
            if ($branch->is_default) {
                // Unset all other branches as default
                static::where('id', '!=', $branch->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Valida el RFC (tax_id) si está presente.
     */
    protected static function validateTaxId($branch)
    {
        if ($branch->tax_id) {
            // Limpiar RFC
            $rfc = preg_replace('/[\s\-]/', '', strtoupper($branch->tax_id));

            // Validar longitud
            if (strlen($rfc) !== 12 && strlen($rfc) !== 13) {
                throw new \InvalidArgumentException(
                    'El RFC debe tener 12 caracteres (persona moral) o 13 caracteres (persona física).'
                );
            }

            // Validar formato básico
            if (! preg_match('/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/', $rfc)) {
                throw new \InvalidArgumentException(
                    'El formato del RFC no es válido.'
                );
            }
        }
    }

    /**
     * Valida el código de centro de costo.
     */
    protected static function validateCeco($branch)
    {
        if (empty($branch->ceco)) {
            throw new \InvalidArgumentException('El código de centro de costo es obligatorio.');
        }

        // Validar que no contenga espacios
        if (strpos($branch->ceco, ' ') !== false) {
            throw new \InvalidArgumentException(
                'El código de centro de costo no puede contener espacios.'
            );
        }

        // Validar longitud mínima
        if (strlen($branch->ceco) < 3) {
            throw new \InvalidArgumentException(
                'El código de centro de costo debe tener al menos 3 caracteres.'
            );
        }
    }

    /**
     * Mutator para name: Capitaliza correctamente el nombre
     */
    public function setNameAttribute($value)
    {
        if ($value) {
            // Capitalizar cada palabra
            $this->attributes['name'] = ucwords(strtolower($value));
        } else {
            $this->attributes['name'] = null;
        }
    }

    /**
     * Mutator para ceco: Convierte a mayúsculas y limpia formato
     */
    public function setCecoAttribute($value)
    {
        if ($value) {
            // Convertir a mayúsculas y remover espacios
            $this->attributes['ceco'] = strtoupper(preg_replace('/\s/', '', $value));
        } else {
            $this->attributes['ceco'] = null;
        }
    }

    /**
     * Mutator para tax_id: Convierte a mayúsculas y limpia formato
     */
    public function setTaxIdAttribute($value)
    {
        if ($value) {
            // Convertir a mayúsculas y remover espacios/guiones
            $this->attributes['tax_id'] = strtoupper(preg_replace('/[\s\-]/', '', $value));
        } else {
            $this->attributes['tax_id'] = null;
        }
    }

    /**
     * Scope to search branches by name, ceco or tax_id.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('ceco', 'like', "%{$search}%")
                ->orWhere('tax_id', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to filter by centro de costo.
     */
    public function scopeByCeco($query, $ceco)
    {
        return $query->where('ceco', strtoupper($ceco));
    }

    /**
     * Scope to get branches with tax_id.
     */
    public function scopeWithTaxId($query)
    {
        return $query->whereNotNull('tax_id');
    }

    /**
     * Scope to get branches without tax_id.
     */
    public function scopeWithoutTaxId($query)
    {
        return $query->whereNull('tax_id');
    }

    /**
     * Check if this branch has a tax ID.
     */
    public function hasTaxId(): bool
    {
        return ! is_null($this->tax_id);
    }

    /**
     * Get the formatted tax ID (RFC).
     */
    public function getFormattedTaxIdAttribute(): ?string
    {
        if (! $this->tax_id) {
            return null;
        }

        $rfc = $this->tax_id;

        // Formatear RFC según su longitud
        if (strlen($rfc) === 12) {
            // Persona moral: AAA000000AAA -> AAA-000000-AAA
            return substr($rfc, 0, 3).'-'.substr($rfc, 3, 6).'-'.substr($rfc, 9, 3);
        } elseif (strlen($rfc) === 13) {
            // Persona física: AAAA000000AAA -> AAAA-000000-AAA
            return substr($rfc, 0, 4).'-'.substr($rfc, 4, 6).'-'.substr($rfc, 10, 3);
        }

        return $rfc;
    }

    /**
     * Get the display name for this branch.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->ceco})";
    }

    /**
     * Get the full display name with tax ID.
     */
    public function getFullDisplayNameAttribute(): string
    {
        $taxInfo = $this->tax_id ? " - RFC: {$this->formatted_tax_id}" : '';

        return "{$this->name} ({$this->ceco}){$taxInfo}";
    }

    /**
     * Get branch type based on tax_id presence.
     */
    public function getTypeAttribute(): string
    {
        return $this->hasTaxId() ? 'Sucursal Fiscal' : 'Centro de Costo';
    }

    /**
     * Find branch by centro de costo.
     */
    public static function findByCeco(string $ceco): ?self
    {
        return static::where('ceco', strtoupper($ceco))->first();
    }

    /**
     * Find branch by tax ID.
     */
    public static function findByTaxId(string $taxId): ?self
    {
        $cleanTaxId = strtoupper(preg_replace('/[\s\-]/', '', $taxId));

        return static::where('tax_id', $cleanTaxId)->first();
    }

    /**
     * Get common branch examples.
     */
    public static function getCommonBranches(): array
    {
        return [
            [
                'name' => 'Oficina Matriz',
                'ceco' => 'MTZ001',
                'tax_id' => 'ABC123456789',
            ],
            [
                'name' => 'Sucursal Norte',
                'ceco' => 'NTE002',
                'tax_id' => 'ABC123456789',
            ],
            [
                'name' => 'Sucursal Sur',
                'ceco' => 'SUR003',
                'tax_id' => 'ABC123456789',
            ],
            [
                'name' => 'Centro Occidente',
                'ceco' => 'OCC004',
                'tax_id' => null,
            ],
            [
                'name' => 'Centro Oriente',
                'ceco' => 'ORI005',
                'tax_id' => null,
            ],
            [
                'name' => 'Almacén Central',
                'ceco' => 'ALM006',
                'tax_id' => null,
            ],
            [
                'name' => 'Departamento TI',
                'ceco' => 'TEC007',
                'tax_id' => null,
            ],
            [
                'name' => 'Recursos Humanos',
                'ceco' => 'RRH008',
                'tax_id' => null,
            ],
        ];
    }

    /**
     * Validate RFC checksum (optional advanced validation).
     */
    public function validateRfcChecksum(): bool
    {
        if (! $this->tax_id) {
            return true; // No RFC to validate
        }

        $rfc = $this->tax_id;

        // Esta es una validación básica del dígito verificador
        // En producción se podría implementar el algoritmo completo del SAT
        $validationTable = '0123456789ABCDEFGHIJKLMN&OPQRSTUVWXYZ Ñ';
        $sum = 0;

        for ($i = 0; $i < strlen($rfc) - 1; $i++) {
            $position = strpos($validationTable, $rfc[$i]);
            if ($position !== false) {
                $sum += $position * (strlen($rfc) - $i);
            }
        }

        $remainder = $sum % 11;
        $expectedDigit = $remainder < 2 ? $remainder : 11 - $remainder;
        $lastChar = substr($rfc, -1);

        return $lastChar == $expectedDigit || ($expectedDigit == 10 && $lastChar == 'A');
    }

    /**
     * Scope to get the default branch.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to get non-default branches.
     */
    public function scopeNonDefault($query)
    {
        return $query->where('is_default', false);
    }

    /**
     * Get the default branch (static method for convenience).
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Check if this branch is the default one.
     */
    public function isDefault(): bool
    {
        return $this->is_default === true;
    }

    /**
     * Set this branch as the default one.
     */
    public function setAsDefault(): bool
    {
        $this->is_default = true;
        return $this->save();
    }

    /**
     * Remove default status from this branch.
     */
    public function removeAsDefault(): bool
    {
        $this->is_default = false;
        return $this->save();
    }
}
