<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\SaveMessageSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            UsersTableSeeder::class,
            MessageSeeder::class,
            FriendListSeeder::class,
            SaveMessageSeeder::class
        ]);
    }
}
