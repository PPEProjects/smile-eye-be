<?php

namespace Database\Factories;

use App\Models\GeneralInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

class GeneralInfoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GeneralInfo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
//        $tasked_at = $this->faker->dateTimeBetween('+0 days', '+1 month');
//        $tasked_end = $this->faker->dateTimeBetween($tasked_at, $tasked_at->modify('+'.rand(1, 1000).' day'));
        $repeat = array('everyday','everyweek in today');
        $status=array('public','private');
        return [
            'user_id'=>rand(1,20),
            'todolist_id'=>rand(1,20),
            'goal_id'=>rand(1,20),
            'tasks_id'=>rand(1,20),
            'repeat'=>$repeat,
            'location'=> $this->faker->text(250),
            'friend_ids'=>[rand(1,20)],
            'note_id'=>rand(2,20),
            'attachment_id'=>rand(1,20),
            'status'=>$status
        ];
    }
}
