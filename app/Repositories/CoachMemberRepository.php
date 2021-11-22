<?php

namespace App\Repositories;

use App\Models\CoachMember;
use App\Models\Goal;
use App\Models\GoalMember;
use App\Models\JapaneseGoal;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\MediaService;

class CoachMemberRepository
{
    public function createCoachMember($args)
    {
        $args["user_id"] = Auth::id();
        $args = array_diff_key($args, array_flip(['teacher_id']));
        return CoachMember::create($args);
    }
    public function addCoachMember($args)
    {
        if(!isset($args['user_id'])){
            $args['user_id'] = Auth::id();
        }
        $addMember[] =  CoachMember::updateOrCreate(
                            ['user_id' => $args['user_id']],
                            $args
                            );
        return  $addMember;
    }
    public function updateCoachMember($args)
    {
        $update = tap(CoachMember::findOrFail($args["id"]))
                ->update($args);
        return $update;
    }
    public function deleteCoachMember($args)
    {
        $delete = CoachMember::find($args['id']);
        return $delete->delete();
    }
    public function deleteMyMember($args)
    {
        $user_id = Auth::id();
        $coachMember = CoachMember::where('user_id',$args['user_id'])->first();
        $delete = array_diff(@$coachMember->teacher_ids ?? [], [$user_id]);
        $update = tap(CoachMember::findOrFail($coachMember->id))
                            ->update(['teacher_ids' => $delete]);
        return  $update ;
    }
    public function myListCoachMembers($args)
    {
        $userId = Auth::id();
        $coachMember = CoachMember::where('user_id', $userId)->first();
        $listMembers = GoalMember::SelectRaw("id, goal_id, add_user_id as user_id, teacher_id")
                                    ->whereIn('goal_id', $coachMember->goal_ids)
                                    ->get();
        $listMembers = $listMembers->map(function($list) {
            $list->goals = $this->findGoalIds([$list->goal_id]);
            return $list;
        });
        return $listMembers;
    }
    public function findGoalIds($ids, $number = 1){
        $status = ["trial","paid", "paying", "trouble", "paid and complete"];
        $goals = Goal::selectRaw("*,start_day AS started_at_a_goal")->whereIn('id', $ids)->get();
        $goals = $goals->map(function($goal) use($status, $number){
            $goal->count_missing = [
                "call" => random_int(0,10),
                "message" => random_int(0,10),
                "pratice" => $number
            ];
            $goal->status = $status[random_int(0,(count($status) - 1))];
            return $goal;
        });
        return $goals;
    }

    public function myListSupportMembers($args)
    {
        return $this->myListCoachMembers($args);
    }
}
