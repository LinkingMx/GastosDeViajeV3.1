<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
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
        'authorizer_id',
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
     * Get the default authorizer for this department.
     */
    public function authorizer()
    {
        return $this->belongsTo(User::class, 'authorizer_id');
    }

    /**
     * Alias for the authorizer relationship (for clarity in code)
     */
    public function defaultAuthorizer()
    {
        return $this->authorizer();
    }

    /**
     * Get all users that belong to this department.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all users that have this department as their authorizer.
     */
    public function authorizedUsers()
    {
        return $this->hasMany(User::class, 'department_id');
    }

    /**
     * Scope to get departments with their authorizer.
     */
    public function scopeWithAuthorizer($query)
    {
        return $query->with('authorizer');
    }

    /**
     * Scope to get active departments (with at least one user).
     */
    public function scopeActive($query)
    {
        return $query->whereHas('users');
    }
}
