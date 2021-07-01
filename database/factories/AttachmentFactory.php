<?php

namespace Database\Factories;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attachment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $ran = array('Video','Image','Audio');
        $k = array_rand($ran);
        $v = $ran[$k];

        return [

            'user_id'=>rand(1,20),
            'file_type' => $v,
            'file'=>$this->faker->text(10)


        ];
    }
}
