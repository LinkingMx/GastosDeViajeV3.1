<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
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
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get all employees that have this position.
     */
    public function employees()
    {
        return $this->hasMany(User::class, 'position_id');
    }

    /**
     * Get all users that have this position (alias for employees).
     */
    public function users()
    {
        return $this->hasMany(User::class, 'position_id');
    }

    /**
     * Get all per diems associated with this position.
     * (Relation will be complete when PerDiem model is created)
     */
    public function perDiems()
    {
        return $this->hasMany(PerDiem::class);
    }

    /**
     * Scope to get positions with their employees count.
     */
    public function scopeWithEmployeesCount($query)
    {
        return $query->withCount('employees');
    }

    /**
     * Scope to get active positions (with at least one employee).
     */
    public function scopeActive($query)
    {
        return $query->whereHas('employees');
    }

    /**
     * Scope to search positions by name or description.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Get the display name for this position.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name.($this->employees_count ? " ({$this->employees_count} empleados)" : '');
    }
}
