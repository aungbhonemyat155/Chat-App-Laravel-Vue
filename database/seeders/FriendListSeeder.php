<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FriendListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $count = 1;
        foreach (range(1, 30) as $index) {
            DB::table('friend_lists')->insert([
                'first_user_id' => 1,
                'second_user_id' => $index+1,
                'is_approve' => true,
                'created_at' => Carbon::now()->addMinutes($count),
                'updated_at' => Carbon::now()->addMinutes($count)
            ]);
            $count++;
        }
    }
}
