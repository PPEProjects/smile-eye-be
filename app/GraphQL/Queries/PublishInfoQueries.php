<?php
namespace App\GraphQL\Queries ;

use App\Models\User;
use Illuminate\Support\Facades\Auth;


class PublishInfoQueries
{


    public function my_publishInfos()
    {
        $id = Auth::id();
        return User::find($id)->publishs;
    }
}