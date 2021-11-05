<?php

namespace App\Repositories;

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
    public function listGoalTemplates(){
        return GoalTemplate::all();
    }
}