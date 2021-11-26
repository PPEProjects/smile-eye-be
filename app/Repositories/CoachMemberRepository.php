<?php

namespace App\Repositories;

use App\Models\CoachMember;
use App\Models\Goal;
use App\Models\GoalMember;
use App\Models\JapaneseGoal;
use App\Models\Notification;
use App\Models\Payment;
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
    public function addGoalsToMyTeacher($args)
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
        $listMembers = GoalMember::SelectRaw("id, goal_id, add_user_id as user_id, teacher_id")
                                    ->where('teacher_id',  $userId)
                                    ->get();
        $memberGroupByGoals = [];
        $typeNotis = ["diary", "achieve", "edit_diary", "communication"];

        foreach($listMembers as $value)
        {
            $numberMember = GoalMember::SelectRaw('Count(teacher_id) as number_member')
                                        ->where('teacher_id', $value->user_id)
                                        ->first();

            $notification = Notification::selectRaw("count(*) as count")
                                        ->where("user_id", $value->user_id)
                                        ->where("user_receive_id", Auth::id())
                                        ->whereIn("type", $typeNotis)
                                        ->whereRaw("(is_read is null or is_read = 0)")
                                        ->first()
                                        ->toArray();
            $countMissing = [
                            'message' => 0,
                            'call' => 0,
                            'notification' => @$notification['count'] ?? 0
                            ];

            $member = User::where('id', $value->user_id)->first();
            $member->number_member = @$numberMember->number_member ?? 0;
            $member->count_missing = $countMissing;
            $memberGroupByGoals[$value->goal_id][] =  @$member;         
        }
        $getIds = $listMembers->pluck('goal_id');
        $goals = Goal::SelectRaw("id, user_id, name")
                        ->whereIn('id',@$getIds ?? [])
                        ->get();

        $coachMembers = $goals->map(function($goal) use ($memberGroupByGoals){
            $goal->members = @$memberGroupByGoals[$goal->id];
            return $goal; 
        }); 
        return $coachMembers;
    }
    
    public function myListSupportMembers($args)
    {
        $userId = Auth::id();
        $coach = CoachMember::where('user_id', $userId)->first();
        $payment = Payment::whereIn('goal_id', @$coach->goal_ids ?? [])->get();
        return $payment;
    }
}
