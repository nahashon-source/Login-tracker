<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class InteractiveSignInFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'username' => $this->faker->safeEmail,
            'date_utc' => now(),
            'system' => 'SCM',
        ];
    }
}
