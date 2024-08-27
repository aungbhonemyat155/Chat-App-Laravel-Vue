<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SaveMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i = 0; $i < 50; $i++){
            DB::table('save_messages')->insert([
                "message" => fake()->sentence(10),
                "user_id" => 1,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);
        }
    }
}
