<?php
namespace App\GraphQL\Queries ;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LogQueries
{
    public function my_logs()
    {
        $id = Auth::id();
        return User::find($id)->logs;
    }
}