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
        $goalMembers = GoalMember::where('add_user_id', Auth::id())->get();
        return $goalMembers;
    }
    public function detailGoalMembers($args)
    {
        return GoalMember::find($args['id']);
    }
   public function deleteGoalMemberByGoalId($args){
        $goalMembers = GoalMember::where('goal_id', $args['goal_id'])
                                    ->where('add_user_id', Auth::id())
                                    ->first();
        if(isset($goalMembers)){
            return $goalMembers->delete();
        }
        return false;
   }

   public function CountNumberMemberGoal($idGoal)
    {
       return GoalMember::selectRaw("COUNT(goal_id) as `number_member`")
                        ->where('goal_id', $idGoal)
                        ->first();

    }
}
