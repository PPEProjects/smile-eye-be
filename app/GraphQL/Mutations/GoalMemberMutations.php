<?php
namespace App\GraphQL\Mutations;
use App\Models\GoalMember;
use App\Repositories\GoalMemberRepository;
use Illuminate\Support\Facades\Auth;

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


    public function upsertGoalMember($_, array $args)
    {
        $args['user_id'] = Auth::id();
        $goalMember = GoalMember::updateOrCreate(
            [
                'add_user_id' => @$args['add_user_id'],
                'goal_id' => @$args['goal_id'],
            ],
            $args
        );
        return $goalMember;
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
