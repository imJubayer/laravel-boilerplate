<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use LucasDotVin\Soulbscription\Models\Feature;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Feature::create([
            'consumable'       => true,
            'name'             => 'manage-tasks-limited',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity'      => 10,
        ]);

        Feature::create([
            'consumable'       => false,
            'name'             => 'manage-tasks-unlimited',
        ]);
    }
}
