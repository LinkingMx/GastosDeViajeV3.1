<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerDiem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'position_id',
        'detail_id',
        'scope',
        'currency',
        'amount',
        'valid_from',
        'valid_to',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'valid_from' => 'date',
            'valid_to' => 'date',
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
        static::creating(function ($perDiem) {
            static::validateDateRange($perDiem);
            static::validateCurrency($perDiem);
            static::validateScope($perDiem);
        });

        // Validación antes de actualizar
        static::updating(function ($perDiem) {
            static::validateDateRange($perDiem);
            static::validateCurrency($perDiem);
            static::validateScope($perDiem);
        });
    }

    /**
     * Valida el rango de fechas.
     */
    protected static function validateDateRange($perDiem)
    {
        if ($perDiem->valid_to && $perDiem->valid_from > $perDiem->valid_to) {
            throw new \InvalidArgumentException(
                'La fecha de inicio debe ser anterior o igual a la fecha de fin.'
            );
        }
    }

    /**
     * Valida el código de moneda ISO-4217.
     */
    protected static function validateCurrency($perDiem)
    {
        $validCurrencies = ['MXN', 'USD', 'EUR', 'CAD', 'GBP', 'JPY', 'CHF', 'AUD'];

        if (! in_array(strtoupper($perDiem->currency), $validCurrencies)) {
            throw new \InvalidArgumentException(
                "La moneda '{$perDiem->currency}' no es válida. Monedas permitidas: ".implode(', ', $validCurrencies)
            );
        }
    }

    /**
     * Valida el scope.
     */
    protected static function validateScope($perDiem)
    {
        $validScopes = ['domestic', 'foreign'];

        if (! in_array($perDiem->scope, $validScopes)) {
            throw new \InvalidArgumentException(
                "El alcance '{$perDiem->scope}' no es válido. Valores permitidos: ".implode(', ', $validScopes)
            );
        }
    }

    /**
     * Mutator para currency: Convierte a mayúsculas
     */
    public function setCurrencyAttribute($value)
    {
        $this->attributes['currency'] = strtoupper($value);
    }

    /**
     * Mutator para scope: Convierte a minúsculas
     */
    public function setScopeAttribute($value)
    {
        $this->attributes['scope'] = strtolower($value);
    }

    /**
     * Get the position that this per diem belongs to.
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the expense detail that this per diem is associated with.
     */
    public function detail()
    {
        return $this->belongsTo(ExpenseDetail::class, 'detail_id');
    }

    /**
     * Scope to get current per diems (valid today).
     */
    public function scopeCurrent($query)
    {
        $today = Carbon::today();

        return $query->where('valid_from', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $today);
            });
    }

    /**
     * Scope to get per diems valid for a specific date.
     */
    public function scopeValidOn($query, $date)
    {
        return $query->where('valid_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $date);
            });
    }

    /**
     * Scope to filter by position.
     */
    public function scopeByPosition($query, $positionId)
    {
        return $query->where('position_id', $positionId);
    }

    /**
     * Scope to filter by scope (domestic/foreign).
     */
    public function scopeByScope($query, $scope)
    {
        return $query->where('scope', $scope);
    }

    /**
     * Scope to filter by currency.
     */
    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', strtoupper($currency));
    }

    /**
     * Scope to get domestic per diems.
     */
    public function scopeDomestic($query)
    {
        return $query->where('scope', 'domestic');
    }

    /**
     * Scope to get foreign per diems.
     */
    public function scopeForeign($query)
    {
        return $query->where('scope', 'foreign');
    }

    /**
     * Scope to get per diems with their relationships.
     */
    public function scopeWithRelations($query)
    {
        return $query->with(['position', 'detail.concept']);
    }

    /**
     * Scope to get expired per diems.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('valid_to')
            ->where('valid_to', '<', Carbon::today());
    }

    /**
     * Scope to get future per diems (not yet valid).
     */
    public function scopeFuture($query)
    {
        return $query->where('valid_from', '>', Carbon::today());
    }

    /**
     * Scope to get active per diems (not expired).
     */
    public function scopeActive($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('valid_to')
                ->orWhere('valid_to', '>=', Carbon::today());
        });
    }

    /**
     * Check if this per diem is currently valid.
     */
    public function isValid($date = null): bool
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::today();

        if ($this->valid_from > $checkDate) {
            return false;
        }

        if ($this->valid_to && $this->valid_to < $checkDate) {
            return false;
        }

        return true;
    }

    /**
     * Check if this per diem is expired.
     */
    public function isExpired(): bool
    {
        return $this->valid_to && $this->valid_to < Carbon::today();
    }

    /**
     * Check if this per diem is for domestic travel.
     */
    public function isDomestic(): bool
    {
        return $this->scope === 'domestic';
    }

    /**
     * Check if this per diem is for foreign travel.
     */
    public function isForeign(): bool
    {
        return $this->scope === 'foreign';
    }

    /**
     * Get the formatted amount with currency.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2).' '.$this->currency;
    }

    /**
     * Get the scope as a readable string.
     */
    public function getScopeDisplayAttribute(): string
    {
        return $this->scope === 'domestic' ? 'Nacional' : 'Extranjero';
    }

    /**
     * Get the validity period as a readable string.
     */
    public function getValidityPeriodAttribute(): string
    {
        $from = $this->valid_from->format('d/m/Y');
        $to = $this->valid_to ? $this->valid_to->format('d/m/Y') : 'Indefinido';

        return "Desde {$from} hasta {$to}";
    }

    /**
     * Get the display name for this per diem.
     */
    public function getDisplayNameAttribute(): string
    {
        $position = $this->position ? $this->position->name : 'Sin posición';
        $detail = $this->detail ? $this->detail->name : 'Sin detalle';

        return "{$position} - {$detail} ({$this->scope_display})";
    }

    /**
     * Get available currencies.
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
        ];
    }

    /**
     * Get available scopes.
     */
    public static function getAvailableScopes(): array
    {
        return [
            'domestic' => 'Nacional',
            'foreign' => 'Extranjero',
        ];
    }

    /**
     * Find applicable per diem for a specific position, detail, scope and date.
     */
    public static function findApplicable($positionId, $detailId, $scope, $currency = 'MXN', $date = null): ?self
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::today();

        return static::where('position_id', $positionId)
            ->where('detail_id', $detailId)
            ->where('scope', $scope)
            ->where('currency', strtoupper($currency))
            ->validOn($checkDate)
            ->orderBy('valid_from', 'desc')
            ->first();
    }

    /**
     * Calculate total per diem for a period.
     */
    public function calculateForPeriod(Carbon $startDate, Carbon $endDate): float
    {
        $days = $startDate->diffInDays($endDate) + 1; // Include both start and end dates

        return $this->amount * $days;
    }
}
