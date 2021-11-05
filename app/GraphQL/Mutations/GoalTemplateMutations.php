<?php

namespace App\GraphQL\Mutations;

use App\Models\GoalTemplate;
use App\Repositories\GoalTemplateRepository;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

class GoalTemplateMutations{

    private $goal_score_repository;

    public function __construct(GoalTemplateRepository $goal_score_repository)
    {
        $this->goal_score_repository = $goal_score_repository;
    }
    public function createGoalTemplate($_,array $args)
    {
       return $this->goal_score_repository->createGoalTemplate($args);
    }
    public function updateGoalTemplate($_,array $args)
    {
        return $this->goal_score_repository->updateGoalTemplate($args);
    }
    public function deleteGoalTemplate($_,array $args)
    {
        return $this->goal_score_repository->deleteGoalTemplate($args);
    }
}