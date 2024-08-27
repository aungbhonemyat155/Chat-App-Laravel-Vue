<?php

namespace Database\Factories;

use App\Models\SaveMessage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaveMessage>
 */
class SaveMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = SaveMessage::class;

    public function definition(): array
    {
        return [
            "message" => fake()->sentence(10),
            "user_id" => 1,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now()
        ];
    }
}
