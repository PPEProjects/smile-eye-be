<?php

namespace Database\Factories;

use App\Models\Friend;
use Illuminate\Database\Eloquent\Factories\Factory;

class FriendFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Friend::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id'        => 2001,
            'user_id_friend' => rand(1, 200),
            'status'         => $this->faker->randomElement(['pending', 'accept', 'block']),
            'goal_ids'       => [rand(1, 20)],
        ];
    }
}
