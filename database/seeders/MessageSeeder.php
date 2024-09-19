<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (range(1, 100) as $index) {
            if($index%2 === 0){
                DB::table('messages')->insert([
                    'from_user_id' => 1,
                    'to_user_id' => 2,
                    'message' => fake()->sentence(10),
                    'created_at' => Carbon::now(),
                    'friend_lists_id' => 1
                ]);
            }else{
                DB::table('messages')->insert([
                    'from_user_id' => 2,
                    'to_user_id' => 1,
                    'message' => fake()->sentence(10),
                    'created_at' => Carbon::now(),
                    'friend_lists_id' => 1
                ]);
            }
        }
    }
}
