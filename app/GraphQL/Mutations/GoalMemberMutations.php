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
        if (isset($args['goal_ids'])) {
            foreach ($args['goal_ids'] as $goal_id) {
                $arr = array_diff_key($args, array_flip(['goal_id']));
                GoalMember::updateOrCreate(
                    [
                        'add_user_id' => $arr['add_user_id'],
                        'goal_id'     => $goal_id,
                    ],
                    $arr
                );
            }
            return GoalMember::whereIn('goal_id', $args['goal_ids'])
                ->where('add_user_id', $args['add_user_id'])
                ->get();
        }
        $goalMember = GoalMember::updateOrCreate(
            [
                'add_user_id' => @$args['add_user_id'],
                'goal_id'     => @$args['goal_id'],
            ],
            $args
        );

        return [$goalMember];
    }

    public function updateGoalMember($_, array $args)
    {
        return $this->goal_member_repository->updateGoalMember($args);

    }


    public function deleteGoalMember($_, array $args)
    {
        return $this->goal_member_repository->deleteGoalMember($args);
    }
    public function deleteGoalMemberByGoalId($_, array $args)
    {
        return $this->goal_member_repository->deleteGoalMemberByGoalId($args);
    }
}
