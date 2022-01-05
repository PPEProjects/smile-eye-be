<?php

namespace App\Repositories;

use App\Models\GoalScore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class GoalScoreRepository{

    public function upsertGoalScore($args)
    {
        $args['user_id'] = Auth::id();
        if(isset($args['goal_id'])){
            $goalScore = GoalScore::updateOrCreate(
                ['user_id' => $args['user_id'], 'goal_id' => $args['goal_id']],
                $args
            );
            return $goalScore;
        }
        return ;
    }

    public function updateGoalScore($args)
    {    
        $args['user_id'] = Auth::id();     
        return tap(GoalScore::findOrFail($args["id"]))->update($args);
    }

    public function deleteGoalScore($args)
    {
        $GoalScore = GoalScore::find($args['id']);
        return $GoalScore->delete();
    }

    public function detailGoalScore($args){
        if(isset($args['goal_id']))
        {
            $args['user_id'] = Auth::id();
            $goalScore = GoalScore::where('goal_id', $args['goal_id'])
                ->where('user_id', $args['user_id'])
                ->first();
        }
        else
        {
            $goalScore = GoalScore::find($args['id']);
        }
        return $goalScore;
    }

    public function myGoalScore(){     
        return GoalScore::where('user_id',Auth::id())->get();
    }

}