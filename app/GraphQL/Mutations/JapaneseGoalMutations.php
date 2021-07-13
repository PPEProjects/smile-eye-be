<?php

namespace App\GraphQL\Mutations;
use App\Repositories\JapaneseGoalRepository;
use GraphQL\Error\Error;

class JapaneseGoalMutations{
    private $japanese_goal_repository;
    public function __construct(JapaneseGoalRepository $japanese_goal_repository)
    {
        $this->japanese_goal_repository = $japanese_goal_repository;
    }

    public function createJapaneseGoal($_,array $args){
        if (!isset($args['type'])){
            throw new Error('You must input type');
        }
       return $this->japanese_goal_repository->createJapaneseGoal($args);
    }
    public function updateJapaneseGoal($_,array $args){
        return $this->japanese_goal_repository->updateJapaneseGoal($args);
    }
    public function deletejapaneseGoal($_,array $args){
        return $this->japanese_goal_repository->deletejapaneseGoal($args);
    }

}