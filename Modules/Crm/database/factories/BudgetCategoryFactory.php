<?php

namespace Modules\Crm\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Crm\Models\Campaign::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'description' => $this->faker->word()
        ];
    }
}

