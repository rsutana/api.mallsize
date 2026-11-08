<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Lookups\LocationType;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['name' => 'Santos Meat Shop', 'type' => LocationType::PORK_AND_BEEF, 'contact_person' => 'Ramon Santos', 'mobile' => '0917 100 0001', 'email' => 'santosmeat@email.com', 'street' => 'Stall 14, Public Market', 'barangay' => 'Bankerohan', 'city' => 'Davao City', 'province' => 'Davao del Sur', 'zip' => '8000', 'map_url' => 'https://maps.google.com/', 'notes' => 'Hosts 4 trainees per batch for practicals.', 'is_active' => true],
            ['name' => 'Golden Cleaver Meats', 'type' => LocationType::BEEF, 'contact_person' => 'Liza Fernandez', 'mobile' => '0918 100 0002', 'email' => 'goldencleaver@email.com', 'street' => '[Building], McArthur Highway', 'barangay' => 'Toril', 'city' => 'Davao City', 'province' => 'Davao del Sur', 'zip' => '8000', 'map_url' => 'https://maps.google.com/', 'notes' => null, 'is_active' => true],
            ['name' => 'Dela Peña Pork House', 'type' => LocationType::PORK, 'contact_person' => 'Arnel Dela Peña', 'mobile' => '0927 100 0003', 'email' => null, 'street' => 'Stall 3, Panabo City Market', 'barangay' => 'Poblacion', 'city' => 'Panabo City', 'province' => 'Davao del Norte', 'zip' => '8105', 'map_url' => 'https://maps.google.com/', 'notes' => 'Open Mon–Sat, 5 AM – 2 PM.', 'is_active' => true],
            ['name' => 'Prime Cuts Butchery', 'type' => LocationType::PORK_AND_BEEF, 'contact_person' => 'Joy Villanueva', 'mobile' => '0905 100 0004', 'email' => 'primecuts@email.com', 'street' => '[Street address]', 'barangay' => 'Visayan Village', 'city' => 'Tagum City', 'province' => 'Davao del Norte', 'zip' => '8100', 'map_url' => null, 'notes' => null, 'is_active' => true],
            ['name' => 'Fresh Choice Meat Supply', 'type' => LocationType::PROCESSED_MEATS, 'contact_person' => 'Ben Aquino', 'mobile' => '0916 100 0005', 'email' => null, 'street' => '[Street address]', 'barangay' => 'Zone 2', 'city' => 'Digos City', 'province' => 'Davao del Sur', 'zip' => '8002', 'map_url' => null, 'notes' => 'Partnership on hold until 2027.', 'is_active' => false],
        ];

        foreach ($locations as $location) {
            Location::query()->updateOrCreate(['name' => $location['name']], $location);
        }
    }
}
