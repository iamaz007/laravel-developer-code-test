<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TranslationValue>
 */
class TranslationValueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'locale_code' => fake()->randomElement(['en', 'fr', 'es']),
            'value' => fake()->sentence(3),
            'version' => 1,
        ];
    }
}
