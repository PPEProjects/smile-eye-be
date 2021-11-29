<?php

namespace App\GraphQL\Queries;

use App\Models\Goal;
use App\Models\GoalScore;
use App\Repositories\GoalTemplateRepository;
use Illuminate\Support\Facades\Auth;

class GoalTemplateQueries
{
  private $goal_template_repository;
  public function __construct(GoalTemplateRepository $goal_template_repository)
  {
      $this->goal_template_repository = $goal_template_repository;
  }
 public function myGoalTemplate($_,array $args){
        return $this->goal_template_repository->myGoalTemplate($args);
 }
 public function detailGoalTemplate($_,array $args){
  return $this->goal_template_repository->detailGoalTemplate($args);
 }
 public function listGoalTemplates($_,array $args){
  return $this->goal_template_repository->listGoalTemplates($args);
 }
}