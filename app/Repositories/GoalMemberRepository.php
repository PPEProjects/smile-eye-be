<?php

namespace App\Repositories;

use App\Models\Goal;
use App\Models\GoalMember;
use App\Models\GoalTemplate;
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

    public function goalMembers($args)
    {
        
        $goalMembers = GoalMember::orderBy('id', 'desc')->get();
        
        $checkGoal = $goalMembers->pluck('goal_id');
        $checkUser = $goalMembers->pluck('add_user_id');

        $users = User::whereIn('id', @$checkUser ?? [])->get()->pluck('id');
        $goals = Goal::whereIn('id', @$checkGoal ?? [])->get()->pluck('id');

        $goalMembers = $goalMembers->whereIn('add_user_id', @$users ?? [])
                                    ->whereIn('goal_id', @$goals ?? []);

        $nameAddUser = @$args['name_add_user'];
        $nameGoal = @$args['name_goal'];
        if($nameAddUser){
            $goalMembers =  $goalMembers->filter(function ($goalMember) use ($nameAddUser) {
                return false !== stristr($goalMember->add_user->name, $nameAddUser);
            });
        }
        if($nameGoal){
            $goalMembers =  $goalMembers->filter(function ($goalMember) use ($nameGoal) {
                return false !== stristr($goalMember->goal->name, $nameGoal);
            });
        }
        return $goalMembers;
    }

    public function summaryGoalMembers($args)
    {
        $status = ['accept', 'confirm', "paidConfirmed", 'paid', "done"];
        $goalTemplate = GoalTemplate::whereIn('status', $status)->get();
        $getIds = $goalTemplate->pluck('goal_id');
        $goalMember = GoalMember::selectRaw("*, COUNT(add_user_id) as `number_member`")
                                    ->whereIn('goal_id', @$getIds ?? [])
                                    ->groupByRaw('goal_id, DATE(created_at)')
                                    ->get();
        return  $goalMember;
    }
}
