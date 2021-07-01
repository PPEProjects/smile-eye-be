<?php
namespace App\GraphQL\Queries ;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserAvatarQueries
{
    public function my_userAvatars()
    {
        $id = Auth::id();
        return User::find($id)->userAvatar;
    }
}