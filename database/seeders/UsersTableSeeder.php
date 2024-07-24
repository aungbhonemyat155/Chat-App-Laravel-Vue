<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        foreach (range(1, 100) as $index) {
            DB::table('users')->insert([
                'name' => fake()->name,
                'email' => fake()->safeEmail,
                'password' => bcrypt('password'),
            ]);
        }
    }
}
