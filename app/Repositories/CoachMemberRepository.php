<?php

namespace App\Repositories;

use App\Models\CoachMember;
use App\Models\Goal;
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
        return CoachMember::create($args);
    }
    public function addCoachMember($args)
    {
        foreach($args["user_ids"] as $user_id)
        {
            $addMember[] =  CoachMember::updateOrCreate(
                            ['user_id' => $user_id],
                                $args
                            );
        }
        return  $addMember;
    }
    public function updateCoachMember($args)
    {
        $args["user_id"] = Auth::id();
        $update = tap(CoachMember::findOrFail($args["id"]))
        ->update($args);
        return $update;
    }
    public function deleteCoachMember($args)
    {
        $delete = CoachMember::find($args['id']);
        return $delete->delete();
    }
    public function myListCoachMembers($args)
    {
        $coachMembers = CoachMember::all();
        $goalIds = [];
        $coachIds = [];
        foreach($coachMembers as $value){
            $goals = Goal::whereIn('id', @$value->goal_ids ?? [])->where('user_id', Auth::id())->get();
            $goalIds = array_merge($goalIds, $goals->pluck('id')->toArray()); 
            if(array_intersect($goalIds, @$value->goal_ids ?? [])){
                $coachIds[] = $value->id;
            }
        }
        $coachMember =  CoachMember::WhereIn('id', $coachIds)->get();
        return @$coachMember;
    }
}
