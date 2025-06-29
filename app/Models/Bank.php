<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
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
     * Mutator para code: Convierte a mayúsculas y limpia formato
     */
    public function setCodeAttribute($value)
    {
        if ($value) {
            // Convertir a mayúsculas y remover espacios/caracteres especiales
            $this->attributes['code'] = strtoupper(preg_replace('/[^A-Z0-9]/', '', $value));
        } else {
            $this->attributes['code'] = null;
        }
    }

    /**
     * Mutator para name: Capitaliza correctamente el nombre del banco
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
     * Get all users that use this bank.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope to search banks by code or name.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to get banks with their users count.
     */
    public function scopeWithUsersCount($query)
    {
        return $query->withCount('users');
    }

    /**
     * Scope to get active banks (with at least one user).
     */
    public function scopeActive($query)
    {
        return $query->whereHas('users');
    }

    /**
     * Scope to order by most used banks first.
     */
    public function scopePopular($query)
    {
        return $query->withCount('users')->orderBy('users_count', 'desc');
    }

    /**
     * Get the display name for this bank.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->code})";
    }

    /**
     * Check if this bank code is valid for CLABE validation.
     */
    public function isValidForClabe(): bool
    {
        // El código debe ser exactamente de 3 dígitos para CLABE
        return strlen($this->code) === 3 && is_numeric($this->code);
    }

    /**
     * Validate CLABE for this bank.
     */
    public function validateClabe(string $clabe): bool
    {
        if (! $this->isValidForClabe()) {
            return false;
        }

        // Validar que la CLABE tenga 18 dígitos
        if (strlen($clabe) !== 18 || ! is_numeric($clabe)) {
            return false;
        }

        // Validar que los primeros 3 dígitos coincidan con el código del banco
        return substr($clabe, 0, 3) === $this->code;
    }

    /**
     * Get common Mexican banks as static data.
     */
    public static function getMexicanBanks(): array
    {
        return [
            ['code' => '002', 'name' => 'Banamex'],
            ['code' => '012', 'name' => 'BBVA México'],
            ['code' => '014', 'name' => 'Santander'],
            ['code' => '019', 'name' => 'Banjército'],
            ['code' => '021', 'name' => 'HSBC'],
            ['code' => '030', 'name' => 'Bajío'],
            ['code' => '032', 'name' => 'IXE'],
            ['code' => '036', 'name' => 'Inbursa'],
            ['code' => '037', 'name' => 'Interacciones'],
            ['code' => '042', 'name' => 'Mifel'],
            ['code' => '044', 'name' => 'Scotiabank'],
            ['code' => '058', 'name' => 'Banregio'],
            ['code' => '059', 'name' => 'Invex'],
            ['code' => '060', 'name' => 'Bansi'],
            ['code' => '062', 'name' => 'Afirme'],
            ['code' => '072', 'name' => 'Banorte'],
            ['code' => '103', 'name' => 'American Express'],
            ['code' => '106', 'name' => 'Bank of America'],
            ['code' => '108', 'name' => 'JP Morgan'],
            ['code' => '110', 'name' => 'Credit Suisse'],
            ['code' => '112', 'name' => 'BMONEX'],
            ['code' => '113', 'name' => 'Ve Por Más'],
            ['code' => '116', 'name' => 'ING'],
            ['code' => '124', 'name' => 'Deutsche Bank'],
            ['code' => '126', 'name' => 'Credit Agricole'],
            ['code' => '127', 'name' => 'Azteca'],
            ['code' => '128', 'name' => 'Autofin'],
            ['code' => '129', 'name' => 'Barclays'],
            ['code' => '130', 'name' => 'Compartamos'],
            ['code' => '131', 'name' => 'Banco Famsa'],
            ['code' => '132', 'name' => 'BMULTIVA'],
            ['code' => '133', 'name' => 'Actinver'],
            ['code' => '134', 'name' => 'WAL-MART'],
            ['code' => '135', 'name' => 'NAFIN'],
            ['code' => '136', 'name' => 'Interbanco'],
            ['code' => '137', 'name' => 'BANCOPPEL'],
            ['code' => '138', 'name' => 'ABC Capital'],
            ['code' => '139', 'name' => 'UBS Bank'],
            ['code' => '140', 'name' => 'CONSUBANCO'],
            ['code' => '141', 'name' => 'VOLKSWAGEN'],
            ['code' => '143', 'name' => 'CIBANCO'],
            ['code' => '145', 'name' => 'BBASE'],
            ['code' => '166', 'name' => 'BANSEFI'],
            ['code' => '168', 'name' => 'HIPOTECARIA FEDERAL'],
            ['code' => '600', 'name' => 'MONEXCB'],
            ['code' => '601', 'name' => 'GBM'],
            ['code' => '602', 'name' => 'MASARI'],
            ['code' => '605', 'name' => 'VALUE'],
            ['code' => '606', 'name' => 'ESTRUCTURADORES'],
            ['code' => '607', 'name' => 'TIBER'],
            ['code' => '608', 'name' => 'VECTOR'],
            ['code' => '610', 'name' => 'B&B'],
            ['code' => '614', 'name' => 'ACCIVAL'],
            ['code' => '615', 'name' => 'MERRILL LYNCH'],
            ['code' => '616', 'name' => 'FINAMEX'],
            ['code' => '617', 'name' => 'VALMEX'],
            ['code' => '618', 'name' => 'UNICA'],
            ['code' => '619', 'name' => 'MAPFRE'],
            ['code' => '620', 'name' => 'PROFUTURO'],
            ['code' => '621', 'name' => 'CB ACTINVER'],
            ['code' => '622', 'name' => 'OACTIN'],
            ['code' => '623', 'name' => 'SKANDIA'],
            ['code' => '626', 'name' => 'CBDEUTSCHE'],
            ['code' => '627', 'name' => 'ZURICH'],
            ['code' => '628', 'name' => 'ZURICHVI'],
            ['code' => '629', 'name' => 'SU CASITA'],
            ['code' => '630', 'name' => 'CB INTERCAM'],
            ['code' => '631', 'name' => 'CI BOLSA'],
            ['code' => '632', 'name' => 'BULLTICK CB'],
            ['code' => '633', 'name' => 'STERLING'],
            ['code' => '634', 'name' => 'FINCOMUN'],
            ['code' => '636', 'name' => 'HDI SEGUROS'],
            ['code' => '637', 'name' => 'ORDER'],
            ['code' => '638', 'name' => 'AKALA'],
            ['code' => '640', 'name' => 'CB JPMORGAN'],
            ['code' => '642', 'name' => 'REFORMA'],
            ['code' => '646', 'name' => 'STP'],
            ['code' => '647', 'name' => 'TELECOMM'],
            ['code' => '648', 'name' => 'EVERCORE'],
            ['code' => '649', 'name' => 'SKANDIA'],
            ['code' => '651', 'name' => 'SEGMTY'],
            ['code' => '652', 'name' => 'ASEA'],
            ['code' => '653', 'name' => 'KUSPIT'],
            ['code' => '655', 'name' => 'SOFIEXPRESS'],
            ['code' => '656', 'name' => 'UNAGRA'],
            ['code' => '659', 'name' => 'OPCIONES EMPRESARIALES DEL NOROESTE'],
            ['code' => '901', 'name' => 'CLS'],
            ['code' => '902', 'name' => 'INDEVAL'],
        ];
    }
}
