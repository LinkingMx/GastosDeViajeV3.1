<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Seeding production data...');

        // Create default admin user if none exists
        if (User::count() === 0) {
            $adminEmail = env('ADMIN_EMAIL', 'admin@gastos.com');
            $adminPassword = env('ADMIN_PASSWORD', 'password');

            User::create([
                'name' => 'Administrator',
                'email' => $adminEmail,
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
            ]);

            $this->command->info("✅ Admin user created: {$adminEmail}");
        }

        // Add other production data here
        $this->command->info('✅ Production seeding completed');
    }
}