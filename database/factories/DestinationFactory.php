<?php

namespace Database\Factories;

use App\Models\Itenerary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Destination>
 */
class DestinationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'itenerary_id' => Itenerary::factory(),
            'title' => fake()->city(),
            'address' => fake()->address(),
        ];
    }
}
