<?php

namespace App\GraphQL\Mutations;

use App\Models\Goal;
use App\Models\GoalMember;
use App\Models\GoalRank;
use App\Repositories\GoalRankRepository;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isNull;

class GoalRankMutations
{

//    private $goal_score_repository;
//
//    public function __construct(GoalRankRepository $goal_score_repository)
//    {
//        $this->goal_score_repository = $goal_score_repository;
//    }
//
    public function upsertGoalRank($_, array $args)
    {
        foreach ($args['newIds'] as $key => $id) {
            if(!is_numeric($id)) continue;
            $arr = ['goal_id' => $id, 'pin_index' => $key, 'user_id' => Auth::id()];
            $goalRank = GoalRank::updateOrCreate(
                ['user_id' => $arr['user_id'], 'goal_id' => $arr['goal_id'],],
                $arr
            );
        }
        return true;
    }

    public function updateGoalRank($_, array $args)
    {
//        return $this->goal_score_repository->updateGoalRank($args);

//        Goal::where('id', $args['id'])
//            ->where('user_id', Auth::id())
//            ->update(['rank' => $args['rank']]);
//        GoalMember::where("add_user_id", Auth::id())
//            ->where('goal_id', $args['id'])
//            ->update(['rank' => $args['rank']]);
//        return true;
    }

//    public function deleteGoalRank($_, array $args)
//    {
//        return $this->goal_score_repository->deleteGoalRank($args);
//    }
}