<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


class User extends \ppeCore\dvtinh\Models\User implements MustVerifyEmail
{

    public function friend()
    {
//        return $this->hasMany('App\Models\Friend', 'user_id', 'id');
        return $this->hasMany(Friend::class);
    }

    public function userAvatar()
    {
//        return $this->hasOne('App\Models\User_Avatars', 'user_id', 'id');
        return $this->hasMany(UserAvatar::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class,'user_id','id');
    }
//    public function attachment()
//    {
//        return $this->hasOne(Attachment::class,'id','attachment_id');
//    }
    public  function general_infos(){
        return $this->hasMany(GeneralInfo::class);
    }
    public function logs()
    {
        return $this->hasMany(Log::class);
    }
    public  function goals(){
        return $this->hasMany(Goal::class);
    }
    public function todoLists(){
        return $this->hasMany(Todolist::class);
    }
    public function comments(){
        return $this->hasMany(Comment::class);
    }
    public function tasks(){
        return $this->hasMany(Task::class);
    }
    public function generalInfo(){
        return $this->hasMany(GeneralInfo::class);
    }
    public function notification(){
        return $this->hasMany(Notification::class);
    }
    public function notes(){
        return $this->hasMany(Note::class,'user_id','id');
    }
    public  function japaneseLearn(){
        return $this->hasMany(JapaneseLearn::class);
    }
    public function japanese_goals(){
        return $this->hasMany(JapaneseGoal::class);
    }
    public function coachMember(){
        return $this->hasMany(CoachMember::class);
    }
    public function Payment(){
        return $this->hasMany(Payment::class);
    }
}
