<?php

namespace Database\Factories;

use App\Models\Todolist;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TodolistFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Todolist::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'user_id'    => 2001,
            'goal_id'    => rand(1, 20),
            'name'       => $this->faker->name,
            'status'     => Str::random(50),
            'created_at' => Carbon::now(),
        ];
    }
}
