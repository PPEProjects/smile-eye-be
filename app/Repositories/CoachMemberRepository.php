<?php

namespace App\Repositories;

use App\Models\CoachMember;
use App\Models\Goal;
use App\Models\GoalMember;
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
        foreach($args["user_ids"] as $user_id)
        {
            $coachMember = CoachMember::where('user_id', $user_id)->first();
            $teacherIds = array_diff(@$coachMember->teacher_ids ?? [], [@$args['teacher_id'] ?? (string)Auth::id()]);
            $args['teacher_ids'] = array_merge( @$teacherIds ?? [], [@$args['teacher_id'] ?? (string)Auth::id()]);
            $addMember[] =  CoachMember::updateOrCreate(
                            ['user_id' => $user_id],
                            $args
                            );
        }
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
        $coachMembers = CoachMember::Where('teacher_ids', 'like', '%'.Auth::id().'%')->get();
        $coachMembers = $coachMembers->map(function($coach){
            $coach->goals = $this->findGoalIds(@$coach->goal_ids ?? []);
            return $coach;
        });
        return @$coachMembers;
    }

    public function findGoalIds($ids){
        $status = ["trial","paid", "paying", "trouble", "paid and complete"];
        $goals = Goal::selectRaw("*,start_day AS started_at_a_goal")->whereIn('id', $ids)->get();
        $goals = $goals->map(function($goal) use($status){
            $goal->count_missing = [
                "call" => random_int(0,10),
                "message" => random_int(0,10),
                "pratice" => random_int(0,10)
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
