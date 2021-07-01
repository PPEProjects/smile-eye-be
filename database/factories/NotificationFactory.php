<?php

namespace Database\Factories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {


        $ran1 = array(true , false);
        $H = array_rand($ran1);
        $z = $ran1[$H];
        return [
            'user_id'=>rand(1,20),
            'user_ids'=>[rand(1,20),rand(1,20),rand(1,20)],
            'content' => Str::random(120),
            'is_read' =>$z,
        ];
    }
}
