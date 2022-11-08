<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        //random integer between 1 and 3
        $randomNumber = rand(1, 3);
        $authors = [];
        //iterate based on random number
        for ($i = 0; $i < $randomNumber; $i++) {
            //add random author to array
            $authors[] = fake()->name();
        }


        return [
            'name' => fake()->name(),
            'isbn' => fake()->isbn13(),
            'authors' => json_encode($authors),
            'country' => fake()->country(),
            'number_of_pages' => fake()->numberBetween(100, 1000),
            'publisher' => fake()->company(),
            'release_date' => fake()->date(),
        ];
    }
}
