<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Asset;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\UserRoleEnum;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Rufino John Aguilar',
            'email' => 'aguilarufino@gmail.com',
            'password' => Hash::make('password'), 
            'role' => UserRoleEnum::SUPER_ADMIN,
        ]);

        $this->call(UserSeeder::class);
        User::factory(20)->create();
        Category::factory(10)->create();
        Location::factory(10)->create();
        Manufacturer::factory(10)->create();
        Asset::factory(100)->create();
    }
}
