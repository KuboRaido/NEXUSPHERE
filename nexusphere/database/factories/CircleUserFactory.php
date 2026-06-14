<?php

namespace Database\Factories;

use App\Models\Circle;
use App\Models\Circle_user;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CircleUserFactory extends Factory
{
    protected $model = Circle_user::class;

    public function definition(): array
    {
        return [
            'circle_id' => Circle::factory(),
            'user_id' => User::factory(),
            'role' => 'member',
        ];
    }
}
