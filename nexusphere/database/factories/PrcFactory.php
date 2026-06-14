<?php

namespace Database\Factories;

use App\Models\Prc;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrcFactory extends Factory
{
    protected $model = Prc::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'sentence' => $this->faker->sentence(),
            'type' => 0,
            'circle_id' => null,
            'parent_id' => null,
        ];
    }
}
