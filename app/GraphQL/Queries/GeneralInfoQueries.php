<?php
namespace App\GraphQL\Queries ;

use App\Models\User;
use Illuminate\Support\Facades\Auth;


class GeneralInfoQueries
{


    public function myGeneralInfo()
    {
        $id = Auth::id();
        return User::find($id)->generalInfo;
    }
}