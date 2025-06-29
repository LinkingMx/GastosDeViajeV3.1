<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'iso2',
        'iso3',
        'name',
        'default_currency',
        'is_foreign',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_foreign' => 'boolean',
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
        static::creating(function ($country) {
            static::validateIsoCodes($country);
            static::validateCurrency($country);
        });

        // Validación antes de actualizar
        static::updating(function ($country) {
            static::validateIsoCodes($country);
            static::validateCurrency($country);
        });
    }

    /**
     * Valida los códigos ISO.
     */
    protected static function validateIsoCodes($country)
    {
        // Validar longitud de ISO2
        if (strlen($country->iso2) !== 2) {
            throw new \InvalidArgumentException('El código ISO2 debe tener exactamente 2 caracteres.');
        }

        // Validar longitud de ISO3
        if (strlen($country->iso3) !== 3) {
            throw new \InvalidArgumentException('El código ISO3 debe tener exactamente 3 caracteres.');
        }

        // Validar que sean alfabéticos
        if (! ctype_alpha($country->iso2) || ! ctype_alpha($country->iso3)) {
            throw new \InvalidArgumentException('Los códigos ISO deben contener solo letras.');
        }
    }

    /**
     * Valida el código de moneda si está presente.
     */
    protected static function validateCurrency($country)
    {
        if ($country->default_currency) {
            $validCurrencies = ['MXN', 'USD', 'EUR', 'CAD', 'GBP', 'JPY', 'CHF', 'AUD', 'CNY', 'BRL', 'ARS', 'COP', 'PEN', 'CLP'];

            if (! in_array(strtoupper($country->default_currency), $validCurrencies)) {
                throw new \InvalidArgumentException(
                    "La moneda '{$country->default_currency}' no es válida."
                );
            }
        }
    }

    /**
     * Mutator para iso2: Convierte a mayúsculas
     */
    public function setIso2Attribute($value)
    {
        $this->attributes['iso2'] = strtoupper($value);
    }

    /**
     * Mutator para iso3: Convierte a mayúsculas
     */
    public function setIso3Attribute($value)
    {
        $this->attributes['iso3'] = strtoupper($value);
    }

    /**
     * Mutator para default_currency: Convierte a mayúsculas
     */
    public function setDefaultCurrencyAttribute($value)
    {
        $this->attributes['default_currency'] = $value ? strtoupper($value) : null;
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
     * Scope to get only foreign countries.
     */
    public function scopeForeign($query)
    {
        return $query->where('is_foreign', true);
    }

    /**
     * Scope to get only domestic countries (typically just Mexico).
     */
    public function scopeDomestic($query)
    {
        return $query->where('is_foreign', false);
    }

    /**
     * Scope to search countries by name or ISO codes.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('iso2', 'like', "%{$search}%")
                ->orWhere('iso3', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to filter by currency.
     */
    public function scopeByCurrency($query, $currency)
    {
        return $query->where('default_currency', strtoupper($currency));
    }

    /**
     * Scope to get countries with their cities count.
     */
    public function scopeWithCitiesCount($query)
    {
        return $query->withCount('cities');
    }

    /**
     * Check if this is a foreign country.
     */
    public function isForeign(): bool
    {
        return $this->is_foreign;
    }

    /**
     * Check if this is a domestic country.
     */
    public function isDomestic(): bool
    {
        return ! $this->is_foreign;
    }

    /**
     * Check if this country has a default currency.
     */
    public function hasDefaultCurrency(): bool
    {
        return ! is_null($this->default_currency);
    }

    /**
     * Get the display name for this country.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->iso2})";
    }

    /**
     * Get the full display name with currency.
     */
    public function getFullDisplayNameAttribute(): string
    {
        $currency = $this->default_currency ? " - {$this->default_currency}" : '';

        return "{$this->name} ({$this->iso2}){$currency}";
    }

    /**
     * Get the status as a readable string.
     */
    public function getStatusAttribute(): string
    {
        return $this->is_foreign ? 'Extranjero' : 'Nacional';
    }

    /**
     * Find country by ISO2 code.
     */
    public static function findByIso2(string $iso2): ?self
    {
        return static::where('iso2', strtoupper($iso2))->first();
    }

    /**
     * Find country by ISO3 code.
     */
    public static function findByIso3(string $iso3): ?self
    {
        return static::where('iso3', strtoupper($iso3))->first();
    }

    /**
     * Get Mexico as the domestic country.
     */
    public static function getMexico(): ?self
    {
        return static::findByIso2('MX');
    }

    /**
     * Get common countries for business travel.
     */
    public static function getBusinessTravelCountries(): array
    {
        return [
            // Domestic
            ['iso2' => 'MX', 'iso3' => 'MEX', 'name' => 'México', 'default_currency' => 'MXN', 'is_foreign' => false],

            // North America
            ['iso2' => 'US', 'iso3' => 'USA', 'name' => 'Estados Unidos', 'default_currency' => 'USD', 'is_foreign' => true],
            ['iso2' => 'CA', 'iso3' => 'CAN', 'name' => 'Canadá', 'default_currency' => 'CAD', 'is_foreign' => true],

            // Europe
            ['iso2' => 'DE', 'iso3' => 'DEU', 'name' => 'Alemania', 'default_currency' => 'EUR', 'is_foreign' => true],
            ['iso2' => 'FR', 'iso3' => 'FRA', 'name' => 'Francia', 'default_currency' => 'EUR', 'is_foreign' => true],
            ['iso2' => 'ES', 'iso3' => 'ESP', 'name' => 'España', 'default_currency' => 'EUR', 'is_foreign' => true],
            ['iso2' => 'IT', 'iso3' => 'ITA', 'name' => 'Italia', 'default_currency' => 'EUR', 'is_foreign' => true],
            ['iso2' => 'GB', 'iso3' => 'GBR', 'name' => 'Reino Unido', 'default_currency' => 'GBP', 'is_foreign' => true],
            ['iso2' => 'CH', 'iso3' => 'CHE', 'name' => 'Suiza', 'default_currency' => 'CHF', 'is_foreign' => true],

            // Asia
            ['iso2' => 'CN', 'iso3' => 'CHN', 'name' => 'China', 'default_currency' => 'CNY', 'is_foreign' => true],
            ['iso2' => 'JP', 'iso3' => 'JPN', 'name' => 'Japón', 'default_currency' => 'JPY', 'is_foreign' => true],
            ['iso2' => 'KR', 'iso3' => 'KOR', 'name' => 'Corea del Sur', 'default_currency' => 'KRW', 'is_foreign' => true],
            ['iso2' => 'SG', 'iso3' => 'SGP', 'name' => 'Singapur', 'default_currency' => 'SGD', 'is_foreign' => true],

            // Latin America
            ['iso2' => 'BR', 'iso3' => 'BRA', 'name' => 'Brasil', 'default_currency' => 'BRL', 'is_foreign' => true],
            ['iso2' => 'AR', 'iso3' => 'ARG', 'name' => 'Argentina', 'default_currency' => 'ARS', 'is_foreign' => true],
            ['iso2' => 'CO', 'iso3' => 'COL', 'name' => 'Colombia', 'default_currency' => 'COP', 'is_foreign' => true],
            ['iso2' => 'PE', 'iso3' => 'PER', 'name' => 'Perú', 'default_currency' => 'PEN', 'is_foreign' => true],
            ['iso2' => 'CL', 'iso3' => 'CHL', 'name' => 'Chile', 'default_currency' => 'CLP', 'is_foreign' => true],

            // Oceania
            ['iso2' => 'AU', 'iso3' => 'AUS', 'name' => 'Australia', 'default_currency' => 'AUD', 'is_foreign' => true],
        ];
    }

    /**
     * Get available currencies based on countries.
     */
    public static function getAvailableCurrencies(): array
    {
        return [
            'MXN' => 'Peso Mexicano',
            'USD' => 'Dólar Estadounidense',
            'EUR' => 'Euro',
            'CAD' => 'Dólar Canadiense',
            'GBP' => 'Libra Esterlina',
            'JPY' => 'Yen Japonés',
            'CHF' => 'Franco Suizo',
            'AUD' => 'Dólar Australiano',
            'CNY' => 'Yuan Chino',
            'BRL' => 'Real Brasileño',
            'ARS' => 'Peso Argentino',
            'COP' => 'Peso Colombiano',
            'PEN' => 'Sol Peruano',
            'CLP' => 'Peso Chileno',
            'KRW' => 'Won Surcoreano',
            'SGD' => 'Dólar de Singapur',
        ];
    }
}
