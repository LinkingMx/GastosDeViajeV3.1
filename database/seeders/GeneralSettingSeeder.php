<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GeneralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\GeneralSetting::firstOrCreate(
            ['id' => 1],
            ['dias_minimos_anticipacion' => 5]
        );
    }
}
