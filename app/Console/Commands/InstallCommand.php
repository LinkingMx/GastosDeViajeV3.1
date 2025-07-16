<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    protected $signature = 'app:install {--force : Force installation even if already installed}';
    protected $description = 'Install the application for production use';

    public function handle()
    {
        $this->info('🚀 Installing Gastos de Viaje Application...');

        // Check if already installed
        if (!$this->option('force') && $this->isInstalled()) {
            $this->error('Application is already installed. Use --force to reinstall.');
            return 1;
        }

        // Run migrations
        $this->info('📦 Running migrations...');
        Artisan::call('migrate', ['--force' => true]);
        $this->line(Artisan::output());

        // Create Filament admin user
        $this->info('👤 Creating admin user...');
        $this->createAdminUser();

        // Setup Shield permissions
        if (class_exists(\BezhanSalleh\FilamentShield\Commands\MakeShieldSuperAdminCommand::class)) {
            $this->info('🛡️ Setting up Shield permissions...');
            Artisan::call('shield:install', ['--fresh' => true]);
            $this->line(Artisan::output());
        }

        // Mark as installed
        $this->markAsInstalled();

        $this->info('✅ Application installed successfully!');
        return 0;
    }

    private function createAdminUser()
    {
        $name = $this->ask('Admin name', 'Admin');
        $email = $this->ask('Admin email', 'admin@gastos.com');
        $password = $this->secret('Admin password');

        if (!$password) {
            $password = 'password';
            $this->warn('Using default password: password');
        }

        $user = \App\Models\User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => bcrypt($password),
                'email_verified_at' => now(),
            ]
        );

        $this->info("Admin user created: {$email}");
    }

    private function isInstalled(): bool
    {
        return file_exists(storage_path('app/installed'));
    }

    private function markAsInstalled(): void
    {
        file_put_contents(storage_path('app/installed'), now()->toDateTimeString());
    }
}