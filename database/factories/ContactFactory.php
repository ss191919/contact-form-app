<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = fake('ja_JP');

        return [
            'category_id' => Category::inRandomOrder()->value('id'),

            'first_name' => $faker->firstName(),
            'last_name' => $faker->lastName(),

            'gender' => $faker->numberBetween(1, 3),

            'email' => $faker->safeEmail(),

            // 10〜11桁の数字
            'tel' => $faker->numerify('###########'),

            'address' => $faker->address(),

            'building' => $faker->optional()->secondaryAddress(),

            'detail' => $faker->realText(120),
        ];
    }
}
