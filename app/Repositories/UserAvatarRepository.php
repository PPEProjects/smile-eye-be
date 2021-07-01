<?php

namespace App\Repositories;


use App\Models\UserAvatar;
use Illuminate\Support\Facades\Auth;

class UserAvatarRepository
{

    public function updateUserAvatar($args){
        $args = array_diff_key($args, array_flip(['directive']));
        $update = tap(UserAvatar::where("user_id","=",Auth::id())->first())
            ->update($args);
        return $update;
    }
}
