<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseConcept extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_unmanaged',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_unmanaged' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Mutator para name: Capitaliza correctamente el nombre del concepto
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
     * Get expense details associated with this concept (only if is_unmanaged = true).
     * Note: This relation will be complete when ExpenseDetail model is created.
     */
    public function details()
    {
        return $this->hasMany(ExpenseDetail::class, 'concept_id');
    }

    /**
     * Scope to get only unmanaged concepts (allow manual details).
     */
    public function scopeUnmanaged($query)
    {
        return $query->where('is_unmanaged', true);
    }

    /**
     * Scope to get only managed concepts (predefined structure).
     */
    public function scopeManaged($query)
    {
        return $query->where('is_unmanaged', false);
    }

    /**
     * Scope to search concepts by name or description.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to get concepts with their details count.
     */
    public function scopeWithDetailsCount($query)
    {
        return $query->withCount('details');
    }

    /**
     * Scope to get active concepts (with at least one detail).
     */
    public function scopeActive($query)
    {
        return $query->whereHas('details');
    }

    /**
     * Check if this concept allows manual details.
     */
    public function allowsManualDetails(): bool
    {
        return $this->is_unmanaged;
    }

    /**
     * Check if this concept has predefined structure.
     */
    public function hasPreDefinedStructure(): bool
    {
        return ! $this->is_unmanaged;
    }

    /**
     * Get the display name for this concept.
     */
    public function getDisplayNameAttribute(): string
    {
        $status = $this->is_unmanaged ? 'Manual' : 'Predefinido';

        return "{$this->name} ({$status})";
    }

    /**
     * Get the concept type as a readable string.
     */
    public function getTypeAttribute(): string
    {
        return $this->is_unmanaged ? 'No Gestionado' : 'Gestionado';
    }

    /**
     * Get common expense concepts as static data.
     */
    public static function getCommonConcepts(): array
    {
        return [
            // Conceptos gestionados (estructura predefinida)
            ['name' => 'Hospedaje', 'description' => 'Gastos de hotel, motel o alojamiento temporal', 'is_unmanaged' => false],
            ['name' => 'Alimentación', 'description' => 'Comidas durante el viaje de trabajo', 'is_unmanaged' => false],
            ['name' => 'Transporte Local', 'description' => 'Taxi, Uber, metro, autobús en el destino', 'is_unmanaged' => false],
            ['name' => 'Combustible', 'description' => 'Gasolina para vehículo de empresa o personal', 'is_unmanaged' => false],
            ['name' => 'Peaje', 'description' => 'Casetas de peaje en carreteras', 'is_unmanaged' => false],
            ['name' => 'Estacionamiento', 'description' => 'Parking en aeropuertos, hoteles o centros de trabajo', 'is_unmanaged' => false],
            ['name' => 'Comunicaciones', 'description' => 'Llamadas telefónicas, internet, roaming', 'is_unmanaged' => false],
            ['name' => 'Material de Oficina', 'description' => 'Suministros necesarios para el trabajo', 'is_unmanaged' => false],

            // Conceptos no gestionados (permiten detalles manuales)
            ['name' => 'Gastos Varios', 'description' => 'Gastos diversos no contemplados en otras categorías', 'is_unmanaged' => true],
            ['name' => 'Emergencias Médicas', 'description' => 'Gastos médicos urgentes durante el viaje', 'is_unmanaged' => true],
            ['name' => 'Representación', 'description' => 'Gastos de atención a clientes o proveedores', 'is_unmanaged' => true],
            ['name' => 'Capacitación', 'description' => 'Cursos, seminarios, material educativo', 'is_unmanaged' => true],
            ['name' => 'Extraordinarios', 'description' => 'Gastos excepcionales que requieren justificación especial', 'is_unmanaged' => true],
        ];
    }
}
