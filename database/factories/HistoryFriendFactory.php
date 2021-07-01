<?php

namespace Database\Factories;

use App\Models\HistoryFriend;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistoryFriendFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = HistoryFriend::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id'=>rand(1,20),
            'user_id_friend'=>rand(1,20),
            'goal_id' => rand(1,20),
            'challenge_id' => rand(1,20),
        ];
    }
}
