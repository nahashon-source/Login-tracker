<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'userPrincipalName' => $this->faker->unique()->safeEmail(),
            'displayName' => $this->faker->name(),
            'surname1' => $this->faker->lastName(),
            'surname2' => $this->faker->lastName(),
            'mail1' => $this->faker->safeEmail(),
            'mail2' => $this->faker->safeEmail(),
            'givenName1' => $this->faker->firstName(),
            'givenName2' => $this->faker->firstName(),
            'userType' => 'member',
            'jobTitle' => $this->faker->jobTitle(),
            'department' => $this->faker->word(),
            'accountEnabled' => true,
            'createdDateTime' => now(),
        ];
    }
}
