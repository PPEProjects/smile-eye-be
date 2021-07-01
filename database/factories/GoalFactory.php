<?php

namespace Database\Factories;

use App\Models\Goal;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Goal::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        $start_date = $this->faker->dateTimeBetween('+0 days', '+1 month');
        $end_date = $this->faker->dateTimeBetween($start_date, $start_date->modify('+'.rand(1, 1000).' day'));
        $status = array(todo,done);

        return [
            'user_id'       => 2001,
            'parent_id'     => rand(2, 30),
            'name'          => $this->faker->name,
            'start_day'     => $start_date,
            'end_day'       => $end_date,
            'status' => $status,
            'progress'      => rand(2, 100)
        ];
    }
}
