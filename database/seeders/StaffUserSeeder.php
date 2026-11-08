<?php

namespace Database\Seeders;

use App\Models\Lookups\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class StaffUserSeeder extends Seeder
{
    /**
     * Back-office accounts. Password for both: "password".
     */
    public function run(): void
    {
        User::query()->updateOrCreate(['email' => 'admin@mallsize.ph'], [
            'name' => 'MallSize Admin',
            'mobile' => '0917 000 0001',
            'role' => UserRole::ADMIN,
            'password' => 'password',
        ]);

        User::query()->updateOrCreate(['email' => 'staff@mallsize.ph'], [
            'name' => 'MallSize Staff',
            'mobile' => '0917 000 0002',
            'role' => UserRole::STAFF,
            'password' => 'password',
        ]);
    }
}
