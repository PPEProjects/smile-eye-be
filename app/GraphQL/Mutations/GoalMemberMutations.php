<?php
namespace App\GraphQL\Mutations;
use App\Repositories\GoalMemberRepository;

class GoalMemberMutations
{
    private $goal_member_repository;

    public function __construct(GoalMemberRepository $goal_member_repository)
    {
        $this->goal_member_repository = $goal_member_repository;
    }

    public function createGoalMember($_, array $args)
    {
        return $this->goal_member_repository->createGoalMember($args);
    }


    public function updateGoalMember($_, array $args)
    {
        return $this->goal_member_repository->updateGoalMember($args);

    }


    public function deleteGoalMember($_, array $args)
    {
        return $this->goal_member_repository->deleteGoalMember($args);
    }

}
