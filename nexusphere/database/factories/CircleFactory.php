<?php

namespace Database\Factories;

use App\Models\Circle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CircleFactory extends Factory
{
    protected $model = Circle::class;

    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'circle_name' => $this->faker->unique()->word(),
            'circle_image' => null,
            'sentence' => $this->faker->sentence(),
        ];
    }
}
