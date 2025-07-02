<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AttachmentType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name') && empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    /**
     * Get the travel request attachments for this type.
     */
    public function travelRequestAttachments(): HasMany
    {
        return $this->hasMany(TravelRequestAttachment::class, 'attachment_type_id');
    }

    /**
     * Scope to only include active attachment types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order and name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get options for select fields.
     */
    public static function getSelectOptions(): array
    {
        return static::active()
            ->ordered()
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Get options with additional data for advanced selects.
     */
    public static function getDetailedOptions(): array
    {
        return static::active()
            ->ordered()
            ->get()
            ->mapWithKeys(function ($type) {
                return [
                    $type->id => [
                        'label' => $type->name,
                        'description' => $type->description,
                        'icon' => $type->icon,
                        'color' => $type->color,
                    ],
                ];
            })
            ->toArray();
    }
}
