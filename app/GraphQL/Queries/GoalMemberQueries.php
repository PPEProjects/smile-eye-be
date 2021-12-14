<?php
namespace App\GraphQL\Queries ;

use App\Models\GoalMember;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Repositories\GoalMemberRepository;

class GoalMemberQueries
{
    private $goal_member_repository;
    public function __construct(GoalMemberRepository $goal_member_repository)
    {
        $this->goal_member_repository = $goal_member_repository;
    }

    public function goalMembers($_,array $args)
    {
        return $this->goal_member_repository->goalMembers($args);
    }
    public function myGoalMembers($_,array $args)
    {
        return $this->goal_member_repository->myGoalMembers($args);
    }
    public function detailGoalMembers($_,array $args)
    {
        return $this->goal_member_repository->detailGoalMembers($args);
    }
    public function summaryGoalMembers($_, array $args){
        return $this->goal_member_repository->summaryGoalMembers($args);
      }
}