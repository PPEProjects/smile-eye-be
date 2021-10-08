<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\DailyNote;
use App\Models\Friend;
use App\Models\Goal;
use App\Models\HistoryFriend;
use App\Models\Log;
use App\Models\Attachment;
use App\Models\Note;
use App\Models\Notification;
use App\Models\Todolist;
use App\Models\User;
use App\Models\UserAvatar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

//        Attachment::factory(20)->create();
        // for ($i=51; $i <=100; $i++) {
        //     User::factory()->count(1)->create(
        //         [
        //            'name' => 'user'.$i,
        //            'email' => 'user'.$i.'@gmail.com',
        //            'password' => Hash::make("123456789")
        //         ]
        //     );
        // }
        
//        UserAvatar::factory(20)->create();
//        Friend::factory(20)->create();
//        Goal::factory()->count(1)->create(['parent_id' => null]);
//        for($i=1; $i<=10; $i++){
//            Goal::factory()->count(1)->create(['parent_id' => $i]);
//        }
//        for($i=1; $i<=2; $i++){
//            Goal::factory()->count(1)->create(['parent_id' => 1]);
//        }
//        Goal::factory()->count(2)->create(['parent_id' => null]);
//        Goal::factory(20)->create();
////        Todolist::factory(20)->create();
//        Log::factory(20)->create();
    }
}
