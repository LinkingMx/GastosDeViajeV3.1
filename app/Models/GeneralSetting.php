<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dias_minimos_anticipacion',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'dias_minimos_anticipacion' => 'integer',
    ];

    /**
     * Get the singleton instance of general settings.
     * Creates one if it doesn't exist.
     *
     * @return self
     */
    public static function get(): self
    {
        $setting = static::first();

        if (! $setting) {
            $setting = static::create([
                'dias_minimos_anticipacion' => 5,
            ]);
        }

        return $setting;
    }

    /**
     * Get the minimum days required for travel request anticipation.
     *
     * @return int
     */
    public function getDiasMinimosAnticipacion(): int
    {
        return $this->dias_minimos_anticipacion;
    }

    /**
     * Get the minimum date allowed for travel requests.
     *
     * @return \Carbon\Carbon
     */
    public function getMinimumTravelDate(): \Carbon\Carbon
    {
        return now()->addDays($this->dias_minimos_anticipacion);
    }
}
