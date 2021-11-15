<?php

namespace App\Repositories;

use App\Models\GoalMember;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GoalMemberRepository
{
    public function createGoalMember($args)
    {
        $args["user_id"] = Auth::id();
        return GoalMember::create($args);
    }
    public function updateGoalMember($args)
    {
        return tap(GoalMember::findOrFail($args["id"]))->update($args);
    }
    public function deleteGoalMember($args)
    {
        return GoalMember::where("id",$args["id"])->delete();
    }
    public function myGoalMembers($args)
    {
        return GoalMember::where('user_id', Auth::id())->get();
    }
    public function detailGoalMembers($args)
    {
        return GoalMember::find($args['id']);
    }
   
}
