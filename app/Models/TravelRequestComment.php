<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelRequestComment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'travel_request_id',
        'user_id',
        'comment',
        'type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the travel request that owns this comment.
     */
    public function travelRequest(): BelongsTo
    {
        return $this->belongsTo(TravelRequest::class);
    }

    /**
     * Get the user who made this comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a formatted display of the comment type.
     */
    public function getTypeDisplayAttribute(): string
    {
        return match ($this->type) {
            'submission' => 'Enviada a Autorización',
            'approval' => 'Aprobada',
            'rejection' => 'Rechazada',
            'revision' => 'Puesta en Revisión',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get the CSS class for the comment type badge.
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'submission' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            'approval' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'rejection' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            'revision' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        };
    }
}
