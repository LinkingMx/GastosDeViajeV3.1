<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ExpenseDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'concept_id',
        'name',
        'description',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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
        static::creating(function ($expenseDetail) {
            static::validateUnmanagedConcept($expenseDetail);
        });

        // Validación antes de actualizar
        static::updating(function ($expenseDetail) {
            if ($expenseDetail->isDirty('concept_id')) {
                static::validateUnmanagedConcept($expenseDetail);
            }
        });
    }

    /**
     * Valida que el concepto sea 'unmanaged' (is_unmanaged = true).
     */
    protected static function validateUnmanagedConcept($expenseDetail)
    {
        $concept = ExpenseConcept::find($expenseDetail->concept_id);

        if (! $concept) {
            throw new ModelNotFoundException('El concepto de gasto especificado no existe.');
        }

        if (! $concept->is_unmanaged) {
            throw new \InvalidArgumentException(
                'Los detalles de gasto solo pueden asociarse a conceptos no gestionados (is_unmanaged = true). '.
                "El concepto '{$concept->name}' es un concepto gestionado."
            );
        }
    }

    /**
     * Mutator para name: Capitaliza correctamente el nombre del detalle
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
     * Get the expense concept that this detail belongs to.
     */
    public function concept()
    {
        return $this->belongsTo(ExpenseConcept::class, 'concept_id');
    }

    /**
     * Scope to get only active expense details.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only inactive expense details.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope to filter by concept.
     */
    public function scopeByConcept($query, $conceptId)
    {
        return $query->where('concept_id', $conceptId);
    }

    /**
     * Scope to search details by name or description.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to get details with their concept information.
     */
    public function scopeWithConcept($query)
    {
        return $query->with('concept');
    }

    /**
     * Scope to get details for unmanaged concepts only.
     */
    public function scopeForUnmanagedConcepts($query)
    {
        return $query->whereHas('concept', function ($query) {
            $query->where('is_unmanaged', true);
        });
    }

    /**
     * Check if this detail is currently active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if this detail is currently inactive.
     */
    public function isInactive(): bool
    {
        return ! $this->is_active;
    }

    /**
     * Activate this expense detail.
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate this expense detail.
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Toggle the active status of this expense detail.
     */
    public function toggleStatus(): bool
    {
        return $this->update(['is_active' => ! $this->is_active]);
    }

    /**
     * Get the display name for this detail.
     */
    public function getDisplayNameAttribute(): string
    {
        $status = $this->is_active ? 'Activo' : 'Inactivo';

        return "{$this->name} ({$status})";
    }

    /**
     * Get the full description including concept information.
     */
    public function getFullDescriptionAttribute(): string
    {
        $conceptName = $this->concept ? $this->concept->name : 'Sin concepto';

        return "{$conceptName} - {$this->name}".
               ($this->description ? ": {$this->description}" : '');
    }

    /**
     * Get the status as a readable string.
     */
    public function getStatusAttribute(): string
    {
        return $this->is_active ? 'Activo' : 'Inactivo';
    }

    /**
     * Get common expense details for unmanaged concepts.
     */
    public static function getCommonDetails(): array
    {
        return [
            // Gastos Varios
            'Gastos Varios' => [
                ['name' => 'Propinas de Servicio', 'description' => 'Propinas en hoteles, restaurantes, servicios'],
                ['name' => 'Artículos de Aseo Personal', 'description' => 'Productos de higiene personal durante el viaje'],
                ['name' => 'Medicamentos', 'description' => 'Medicamentos básicos o de emergencia'],
                ['name' => 'Lavandería', 'description' => 'Servicio de lavado y planchado de ropa'],
            ],

            // Emergencias Médicas
            'Emergencias Médicas' => [
                ['name' => 'Consulta Médica de Urgencia', 'description' => 'Atención médica de emergencia'],
                ['name' => 'Medicamentos Recetados', 'description' => 'Medicamentos prescritos por médico'],
                ['name' => 'Estudios Médicos', 'description' => 'Análisis, radiografías u otros estudios'],
                ['name' => 'Traslado Médico', 'description' => 'Ambulancia o traslado a centro médico'],
            ],

            // Representación
            'Representación' => [
                ['name' => 'Comida de Negocios', 'description' => 'Comidas con clientes o proveedores'],
                ['name' => 'Regalos Corporativos', 'description' => 'Obsequios para clientes o socios'],
                ['name' => 'Entretenimiento', 'description' => 'Actividades de entretenimiento empresarial'],
                ['name' => 'Servicios de Protocolo', 'description' => 'Servicios especiales de atención'],
            ],

            // Capacitación
            'Capacitación' => [
                ['name' => 'Inscripción a Curso', 'description' => 'Costo de inscripción a capacitación'],
                ['name' => 'Material Didáctico', 'description' => 'Libros, manuales, material de estudio'],
                ['name' => 'Certificaciones', 'description' => 'Costo de exámenes de certificación'],
                ['name' => 'Software Especializado', 'description' => 'Licencias de software para capacitación'],
            ],

            // Extraordinarios
            'Extraordinarios' => [
                ['name' => 'Multas de Tránsito', 'description' => 'Multas por infracciones de tránsito'],
                ['name' => 'Servicios de Emergencia', 'description' => 'Servicios urgentes no contemplados'],
                ['name' => 'Reparaciones Menores', 'description' => 'Reparaciones urgentes de equipo'],
                ['name' => 'Gestorías', 'description' => 'Trámites urgentes con gestor'],
            ],
        ];
    }
}
