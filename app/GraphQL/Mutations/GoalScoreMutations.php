<?php

namespace App\GraphQL\Mutations;

use App\Models\GoalScore;
use App\Repositories\GoalScoreRepository;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

class GoalScoreMutations{

    private $goal_score_repository;

    public function __construct(GoalScoreRepository $goal_score_repository)
    {
        $this->goal_score_repository = $goal_score_repository;
    }
    public function upsertGoalScore($_,array $args)
    {
       return $this->goal_score_repository->upsertGoalScore($args);
    }
    public function updateGoalScore($_,array $args)
    {
        return $this->goal_score_repository->updateGoalScore($args);
    }
    public function deleteGoalScore($_,array $args)
    {
        return $this->goal_score_repository->deleteGoalScore($args);
    }
}