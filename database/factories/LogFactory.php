<?php

namespace Database\Factories;

use App\Models\Log;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Log::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id'=>rand(1,20),
            'goal_id'=>rand(1,20),
            'todolist_id'=>rand(1,20),
            'content' => $this->faker->text(100),
        ];
    }
}
