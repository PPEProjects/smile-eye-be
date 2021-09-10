<?php

namespace App\GraphQL\Queries;

use App\Models\Goal;
use App\Models\GoalScore;
use App\Repositories\GoalScoreRepository;
use Illuminate\Support\Facades\Auth;

class GoalScoreQueries
{
  private $goal_score_repository;
  public function __construct(GoalScoreRepository $goal_score_repository)
  {
      $this->goal_score_repository = $goal_score_repository;
  }
 public function myGoalScore(){
        return $this->goal_score_repository->myGoalScore();
 }
 public function detailGoalScore($_,array $args){
  return $this->goal_score_repository->detailGoalScore($args);
 }
 
}