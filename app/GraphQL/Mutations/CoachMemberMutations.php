<?php
namespace App\GraphQL\Mutations;

use App\Models\Goal;
use App\Models\GoalMember;
use Illuminate\Support\Facades\Auth;
use App\Repositories\CoachMemberRepository;

class CoachMemberMutations
{
    private $coach_member_repository ;
    public function __construct(CoachMemberRepository $coach_member_repository)
    {
        $this->coach_member_repository = $coach_member_repository;

    }

    public function createCoachMember($_, array $args)
    {
        return $this->coach_member_repository->createCoachMember($args);
    }
    public function addGoalsToMyTeacher($_, array $args){
        return $this->coach_member_repository->addGoalsToMyTeacher($args);
    }
    public function addCoachMember($_, array $args)
    {    $userId = Auth::id();
        $creatGoalMember = [];
        foreach($args['user_ids'] as $addUserId){
            foreach($args['goal_ids'] as $goalId){
                $creatGoalMember[] = GoalMember::updateOrCreate(
                                    [
                                        'add_user_id' => $addUserId,
                                        'goal_id' => $goalId,                                   
                                    ],
                                    [
                                        'add_user_id' => $addUserId,
                                        'goal_id' => $goalId,
                                        'user_id' => $userId,
                                        'teacher_id' => $userId
                                    ]
                                    );
            }
        }
        return $creatGoalMember;
    }
    public function updateCoachMember($_, array $args)
    {
        return $this->coach_member_repository->updateCoachMember($args);
    }
    public function deleteCoachMember($_, array $args)
    {
        return $this->coach_member_repository->deleteCoachMember($args);
    }
    public function deleteMyMember($_, array $args)
    {
        return $this->coach_member_repository->deleteMyMember($args);
    }

}
