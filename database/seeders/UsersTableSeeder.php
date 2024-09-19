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
        DB::table("users")->insert([
            'name' => "Aung Bhone Myat",
            'email' => "aungbhonemyat648@gmail.com",
            'password' => bcrypt('testing1234'),
        ]);

        DB::table("users")->insert([
            'name' => "Testing User",
            'email' => "testing@gmail.com",
            'password' => bcrypt('testing1234'),
        ]);

        DB::table("users")->insert([
            'name' => "Second Testing User",
            'email' => "second@gmail.com",
            'password' => bcrypt('testing1234'),
        ]);

        foreach (range(1, 100) as $index) {
            DB::table('users')->insert([
                'name' => fake()->name,
                'email' => fake()->safeEmail,
                'password' => bcrypt('password'),
            ]);
        }
    }
}
