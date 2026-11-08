<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with the design's sample data.
     * Lookup tables are filled by their migration.
     */
    public function run(): void
    {
        $this->call([
            StaffUserSeeder::class,
            LocationSeeder::class,
            CourseSeeder::class,
            DemoEnrollmentSeeder::class,
            ContentSeeder::class,
        ]);
    }
}
