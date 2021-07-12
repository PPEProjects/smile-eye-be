<?php

namespace App\GraphQL\Queries;


use App\Models\JapaneseGoal;
use App\Repositories\JapaneseGoalRepository;
class JapaneseGoalQueries
{
    private $japanese_goal_repository;

    public function __construct(JapaneseGoalRepository $japanese_goal_repository)
    {
        $this->japanese_goal_repository = $japanese_goal_repository;
    }

    public function detailJapaneseGoal($_,array $args){
     return $this->japanese_goal_repository->detailJapaneseGoal($args);
 }
}