<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class MailConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'mailer',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'is_active',
        'notes',
        'additional_settings',
        'last_tested_at',
        'test_successful',
        'test_message',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'test_successful' => 'boolean',
        'additional_settings' => 'array',
        'last_tested_at' => 'datetime',
        'port' => 'integer',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = auth()->id();
            $model->updated_by = auth()->id();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }

    /**
     * Get the password attribute (decrypt it).
     */
    public function getPasswordAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Set the password attribute (encrypt it).
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get the active configuration.
     */
    public static function getActiveConfiguration()
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Mark this configuration as active and deactivate others.
     */
    public function markAsActive()
    {
        // Deactivate all configurations
        static::where('is_active', true)->update(['is_active' => false]);
        
        // Activate this one
        $this->update(['is_active' => true]);
    }

    /**
     * Get the creator user.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the updater user.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Test the mail configuration.
     */
    public function testConfiguration($recipientEmail = null)
    {
        try {
            // Temporarily set this configuration
            config([
                'mail.default' => $this->mailer,
                'mail.mailers.smtp.host' => $this->host,
                'mail.mailers.smtp.port' => $this->port,
                'mail.mailers.smtp.username' => $this->username,
                'mail.mailers.smtp.password' => $this->password,
                'mail.mailers.smtp.encryption' => $this->encryption,
                'mail.from.address' => $this->from_address,
                'mail.from.name' => $this->from_name,
            ]);

            // Apply additional settings if any
            if ($this->additional_settings) {
                foreach ($this->additional_settings as $key => $value) {
                    config(["mail.{$key}" => $value]);
                }
            }

            // Send test email
            $testEmail = $recipientEmail ?: auth()->user()->email;
            
            \Mail::raw('Esta es una prueba de configuración de correo desde el sistema de Gastos de Viaje.', function ($message) use ($testEmail) {
                $message->to($testEmail)
                    ->subject('Prueba de Configuración de Correo - ' . config('app.name'));
            });

            // Update test results
            $this->update([
                'last_tested_at' => now(),
                'test_successful' => true,
                'test_message' => 'Correo de prueba enviado exitosamente a ' . $testEmail,
            ]);

            return true;
        } catch (\Exception $e) {
            // Update test results with error
            $this->update([
                'last_tested_at' => now(),
                'test_successful' => false,
                'test_message' => 'Error: ' . $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Apply this configuration to the mail system.
     */
    public function applyConfiguration()
    {
        if (!$this->is_active) {
            return;
        }

        config([
            'mail.default' => $this->mailer,
            'mail.mailers.smtp.host' => $this->host,
            'mail.mailers.smtp.port' => $this->port,
            'mail.mailers.smtp.username' => $this->username,
            'mail.mailers.smtp.password' => $this->password,
            'mail.mailers.smtp.encryption' => $this->encryption,
            'mail.from.address' => $this->from_address,
            'mail.from.name' => $this->from_name,
        ]);

        // Apply additional settings if any
        if ($this->additional_settings) {
            foreach ($this->additional_settings as $key => $value) {
                config(["mail.{$key}" => $value]);
            }
        }
    }
}