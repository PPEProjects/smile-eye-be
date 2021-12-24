<?php

namespace App\GraphQL\Mutations;

use App\Models\Goal;
use App\Models\GoalMember;
use App\Models\GoalTemplate;
use App\Models\Payment;
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
        $status = ['accept', 'paused', 'confirmed', "paidConfirmed", "done"];
        if(!isset($args['teacher_id']) && isset($args['goal_id'])){
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
                $payment= Payment::where('goal_id', $goal_id)
                                    ->where("add_user_id", $arr['add_user_id'])
                                    ->first();
                $template = GoalTemplate::where('goal_id', $goal_id)
                                        ->whereIn('status', $status)
                                        ->first();
                if(!isset($payment) && isset($template)){
                    $data = [
                        'goal_id' => $goal_id,
                        'user_id' => $args['user_id'],
                        'add_user_id' => $arr['add_user_id'],
                        'status' => 'trial'
                    ];
                   $createPayment = Payment::create($data);
                }
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
