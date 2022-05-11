<?php

namespace App\Repositories;

use App\Models\Goal;
use App\Models\GoalScore;
use App\Models\JapaneseGoal;
use App\Models\JapaneseLearn;
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
    public function listGoalScore($args){
        $goals = Goal::where('root_id', $args['goal_root_id'])->get();
        $getGoalIds = $goals->pluck('id')->toArray();
        $sumTopic  = $this->findBlock($goals, [$args['goal_root_id']]);
        $jpLearn = JapaneseLearn::whereIn('goal_id', @$getGoalIds ?? [])
                                    ->where('user_id', $args['user_id'])->get();
        $getIdLearns = $jpLearn->pluck('goal_id')->toArray();
        $achievement = $goals->whereIn('id', @$getIdLearns ?? [])
                                ->groupBy('parent_id')
                                ->toArray();
        $japaneseGoal = JapaneseGoal::whereIn('goal_id', @$getGoalIds ?? [])
                                    ->get();
        $totalScore = $japaneseGoal->count('total_score');

        $getIdJP = $japaneseGoal->pluck('goal_id')->toArray();
        $sumTopic = $goals->whereIn('id', @$getIdJP ?? [])
                            ->GroupBy('parent_id')->toArray();

        $goalScore = GoalScore::whereIn('goal_id', $getGoalIds)
                                ->where('user_id', $args['user_id'])->get();
        $list = [];
        $list['score'] = 0;
        $list['total_score'] = $totalScore;
        foreach ($goalScore as $score){
            $list['score'] = $list['score'] + count(@$score->scores ?? []);
        }
        $list['achievement'] = count($achievement);
        $list['sum_topic'] = count($sumTopic);
        return $list;
    }

    public function findBlock($listGoals, $ids, $children = [])
    {
        $getchildren = array_unique($children);
        $goals = $listGoals;
        foreach ($ids as $value) {
            $find = $goals->where('parent_id', $value);
            if ($find->toArray() != []) {
                $idParent = $find->pluck('id')->toArray();
                $getchildren = self::findBlock($listGoals, $idParent, $getchildren);
            } else {
                $checkBlock = $goals->where('id', $value)->first();
                if (isset($checkBlock->japaneseGoal)) {
                    $getchildren[] = @$checkBlock->parent_id;
                }
            }
        }
        return $getchildren;
    }
}