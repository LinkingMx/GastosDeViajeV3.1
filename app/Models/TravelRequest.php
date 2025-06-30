<?php

namespace App\Models;

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
}
