<?php

namespace App\Repositories;

use App\Models\Goal;
use App\Models\GoalTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class GoalTemplateRepository{

    public function createGoalTemplate($args)
    {
        $args['user_id'] = Auth::id();
            return GoalTemplate::create($args);
    }

    public function updateGoalTemplate($args)
    {    
        $args['user_id'] = Auth::id();   
        $args = array_diff_key($args, array_flip(['goal_id']));  
        return tap(GoalTemplate::findOrFail($args["id"]))->update($args);
    }

    public function deleteGoalTemplate($args)
    {
        $GoalTemplate = GoalTemplate::find($args['id']);
        return $GoalTemplate->delete();
    }

    public function detailGoalTemplate($args){
        if(isset($args['goal_id']))
        {
            $GoalTemplate = GoalTemplate::where('goal_id', $args['goal_id'])->first();
        }
        else
        {
            $GoalTemplate = GoalTemplate::find($args['id']);
        }
        return $GoalTemplate;
    }

    public function myGoalTemplate(){     
        return GoalTemplate::where('user_id',Auth::id())->get();
    }
    public function listGoalTemplates($args){
        $status = @$args["status"] ?? "all";
        if($status != "all"){
            $goalTemplate = GoalTemplate::where('status', 'like', $status)->get();
        }
        else{
            $goalTemplate = GoalTemplate::all();
        }
        $goalIds = $goalTemplate->pluck('goal_id');
        $goals = Goal::whereIn('id', @$goalIds ?? [])->get();
        $getId = $goals->pluck('id');
        $goalTemplate = $goalTemplate->whereIn('goal_id', @$getId ?? [])
                                    ->sortByDESC('id'); 
        return @$goalTemplate;
    }
}