<?php

namespace App\GraphQL\Queries;

use App\Models\Goal;
use App\Repositories\GeneralInfoRepository;
use App\Repositories\GoalRepository;
use App\Repositories\TodolistRepository;
use Illuminate\Support\Facades\Auth;

class GoalQueries
{
    private $generalinfo_repository;
    private $goal_repository;
    private $todolist_repository;

    public function __construct(
        GeneralInfoRepository $generalinfo_repository,
        GoalRepository $GoalRepository,
        TodoListRepository $TodoListRepository
    ) {
        $this->generalinfo_repository = $generalinfo_repository;
        $this->goal_repository = $GoalRepository;
        $this->todolist_repository = $TodoListRepository;
    }

    public function goalsChildren($_, array $args)
    {
        $cons = array_merge(['user_id' => Auth::id()], $args);
        $cons = array_intersect_key($cons, array_flip(['user_id', 'goal_id']));
        $goals = $this->goal_repository->getGoalsChildren($cons);
        return $goals;
    }

    public function my_parentGoal($_, array $args)
    {
        $cons = array_merge(['user_id' => Auth::id()], $args);
        dd($cons);
        $cons = array_intersect_key($cons, array_flip(['user_id', 'parent_id', 'id']));
//        dd($cons);
        $goals = $this->goal_repository->getByParentGoal($cons);
//        dd($goals->toArray());
        return $goals;
    }


    public function myGoalsTreeSelect($_, array $args)
    {
        $goals = Goal::selectRaw('id as value, name as title, parent_id')
            ->where("user_id", "=", Auth::id())
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
        return $this->goal_repository->buildTree($goals, null);
    }

# Ver 2
    public function detailGoal($_, array $args)
    {
        $goal = Goal::where('id', $args['id'])->first();
        if ($goal) {
            $generalInfo = $this->generalinfo_repository
                ->setType('goal')
                ->findByTypeId($goal->id);
            $goal->general_info = $generalInfo;
            return $goal;
        }
        return null;
    }

    public function myGoals1($_, array $args)
    {
        return $this->goal_repository->myGoals($args);
    }

    public function myGoals($_, array $args)
    {
        $goals = Goal::where('user_id', Auth::id())
            ->orderBy('id', 'desc');
        switch ($args['parent_id']) {
            case 'all':
                break;
            case 'root':
                $goals = $goals->where('parent_id', null);
                break;
            default:
                $goals = $goals->where('parent_id', $args['parent_id']);
                break;
        }
        $goals = $goals->get();
        $goals = $this->generalinfo_repository
            ->setType('goal')
            ->get($goals)
            ->map(function ($goal) {
                return $this->goal_repository->calculatorProcessTodolist($goal);
            });
//        dd($goals->first()->toArray());
        return $goals;
    }

    public function countGoals($_, array $args)
    {
        return $this->goal_repository->countGoals($args);
    }

    public function myGoalsAchieve($_, array $args)
    {
        return $this->goal_repository->myGoalsAchieve($args);
    }

    public function myGoalsTreeSort($_, array $args)
    {
        if (isset($args['not_auth'])) {
            return $this->goal_repository->getTreeSortByGoalId($args['id']);
        }
        return $this->goal_repository->getTreeSortByGoalId($args['id'], Auth::id());
    }

    public function goalsAchieveTreeSort($_, array $args)
    {
        return $this->goal_repository->goalsAchieveTreeSort($args['id']);
    }
}