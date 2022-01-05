<?php

namespace App\GraphQL\Mutations;

use App\Models\Goal;
use App\Models\GoalMember;
use App\Models\GoalTemplate;
use App\Models\Payment;
use App\Repositories\GoalMemberRepository;
use App\Repositories\PaymentRepository;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

class GoalMemberMutations
{
    private $goal_member_repository;
    private  $payment_repository;
    public function __construct(
        GoalMemberRepository $goal_member_repository,
        PaymentRepository $payment_repository
    )
    {
        $this->goal_member_repository = $goal_member_repository;
        $this->payment_repository = $payment_repository;
    }

    public function createGoalMember($_, array $args)
    {
        return $this->goal_member_repository->createGoalMember($args);
    }


    public function upsertGoalMember($_, array $args)
    {
        $args['user_id'] = Auth::id();
        if(!isset($args['teacher_id']) && isset($args['goal_id'])){
            $goalMember = GoalMember::where('goal_id', $args['goal_id'])
                                        ->where('add_user_id', $args['add_user_id'])
                                        ->first();
            if (isset($goalMember)){
                throw new Error("This goal already exists in your goal list!");
            }
            $goal = Goal::find($args['goal_id']);
            $args['teacher_id'] = @$goal->user_id ?? $args['user_id'];
        }
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
                $this->payment_repository->updateTrial($goal_id, $args['add_user_id']);
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
        if(isset($args['goal_id'])) {
            $this->payment_repository->updateTrial($args['goal_id'], $args['add_user_id']);
        }
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
