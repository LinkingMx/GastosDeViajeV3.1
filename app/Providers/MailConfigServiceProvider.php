<?php

namespace App\Providers;

use App\Models\MailConfiguration;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Solo aplicar configuración después de que las migraciones hayan corrido
        if (Schema::hasTable('mail_configurations')) {
            $this->configureMailFromDatabase();
        }
    }

    /**
     * Configure mail settings from database.
     */
    private function configureMailFromDatabase(): void
    {
        try {
            $activeConfig = MailConfiguration::getActiveConfiguration();
            
            if ($activeConfig) {
                // Configurar los settings principales
                Config::set([
                    'mail.default' => $activeConfig->mailer,
                    'mail.from.address' => $activeConfig->from_address,
                    'mail.from.name' => $activeConfig->from_name,
                ]);

                // Configurar específicamente para SMTP
                if ($activeConfig->mailer === 'smtp') {
                    Config::set([
                        'mail.mailers.smtp.host' => $activeConfig->host,
                        'mail.mailers.smtp.port' => $activeConfig->port,
                        'mail.mailers.smtp.username' => $activeConfig->username,
                        'mail.mailers.smtp.password' => $activeConfig->password,
                        'mail.mailers.smtp.encryption' => $activeConfig->encryption,
                    ]);
                }

                // Aplicar configuraciones adicionales si existen
                if ($activeConfig->additional_settings) {
                    foreach ($activeConfig->additional_settings as $key => $value) {
                        Config::set("mail.{$key}", $value);
                    }
                }
            }
        } catch (\Exception $e) {
            // En caso de error, usar configuración por defecto del .env
            // Log el error si es necesario
            \Log::warning('Error loading mail configuration from database: ' . $e->getMessage());
        }
    }
}