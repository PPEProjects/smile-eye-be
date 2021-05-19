<?php

namespace ppe\dvtinh\Database\Factories;

use App\Models\UserAvatar;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserAvatarFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserAvatar::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        //$user_id = rand(23,40);
        $ran = array('White','Yellow','Black');
        $v = array_rand($ran);
        $color = $ran[$v];
        return [
            'user_id' => rand(1,20),
            'background_file' => Str::random(8).'.'.'jpg',
            'face' => [
                'file' => Str::random(8).'.'.'jpg',
                'color' => $color,
            ],
            'hair' => [
                'file' => Str::random(8).'.'.'jpg',
                'color' => $color,
            ],
            'eyebrows' => [
                'file' => Str::random(8).'.'.'jpg',
                'color' => $color,
            ],
            'body' => [
                'file' => Str::random(8).'.'.'jpg',
                'color' => $color,
            ],
            'eyes' => [
                'file' => Str::random(8).'.'.'jpg',
                'color' => $color,
            ],
            'mouth' => [
                'file' => Str::random(8).'.'.'jpg',
                'color' => $color,
            ],
            'nose' => [
                'file' => Str::random(8).'.'.'jpg',
                'color' => $color,
            ],
            'shirt' => [
                'file' => Str::random(8).'.'.'jpg',
                'color' => $color,
            ],
            'trouser' => [
                'file' => Str::random(8).'.'.'jpg',
                'color' => $color,
            ],
            'shoes' => [
                'file' => Str::random(8).'.'.'jpg',
                'color' => $color,
            ],
        ];
    }
}
