<?php

namespace App\GraphQL\Queries;

use App\Models\Achieve;
use App\Models\Goal;
use App\Models\JapaneseGoal;
use App\Models\JapaneseLearn;
use App\Repositories\GeneralInfoRepository;
use App\Repositories\GoalRepository;
use App\Repositories\JapaneseGoalRepository;
use App\Repositories\JapaneseLearnRepository;
use App\Repositories\TodolistRepository;
use Illuminate\Support\Facades\Auth;

class GoalQueries
{
    private $generalinfo_repository;
    private $goal_repository;
    private $todolist_repository;
    private $japaneseLearn_repository;
    private $japaneseGoal_repository;

    public function __construct(
        GeneralInfoRepository $generalinfo_repository,
        GoalRepository $GoalRepository,
        TodoListRepository $TodoListRepository,
        JapaneseLearnRepository $japaneseLearn_repository,
        JapaneseGoalRepository $japaneseGoal_repository
    ) {
        $this->generalinfo_repository = $generalinfo_repository;
        $this->goal_repository = $GoalRepository;
        $this->todolist_repository = $TodoListRepository;
        $this->japaneseLearn_repository = $japaneseLearn_repository;
        $this->japaneseGoal_repository = $japaneseGoal_repository;
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
            $goalRoot = $this->japaneseGoal_repository->findGoal($goal->parent_id);
        if($goalRoot)
        {
            while (true) 
            {
                if (isset($goalRoot->parent_id) &&  @$goalRoot->parent_id != 0) 
                {
                    $goalRoot =  $this->japaneseGoal_repository->findGoal($goalRoot->parent_id);
                } else {
                    break;
                }
            }
        }
        else
        {
            $goalRoot = $goal;
        }
        $goal->goal_root = $goalRoot;
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

    public function ganttChartSort($_, array $args)
    {
        return $this->goal_repository->ganttChartSort($args['id'], Auth::id());
    }

    public function myGoals($_, array $args)
    {
        $this->goal_repository->calculatorProcessTodolist();
        $this->goal_repository->calculatorProcessUpdate();

        $goals = Goal::where('user_id', Auth::id())
            ->orderByRaw('`rank` ASC, `id` DESC');
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
        $getgoalIds = $goals->pluck('id')->toArray();
        $japaneseGoals = JapaneseGoal::whereIn('goal_id', $getgoalIds)->get();
        if (isset($japaneseGoals) && $args['parent_id'] == 'root') {
            $getIdJapaneseGoals = $japaneseGoals->pluck('goal_id')->toArray();
            $goals = $goals->whereNotIn('id', $getIdJapaneseGoals);
        }
        $goalIds = $goals->pluck('id')->toArray();
        $nextGoal = $this->nextGoal($goalIds);
        $goals = $this->generalinfo_repository
            ->setType('goal')
            ->get($goals);
//            ->map(function ($goal) {
//                return $this->goal_repository->calculatorProcessTodolist($goal);
//            });
//        dd($goals->first()->toArray());
        $goals = $goals->map(function ($goal) use ($nextGoal) {
            $goal->next_goal = @$nextGoal[$goal->id];
            return $goal;
        });
        return $goals;
    }

    public function nextGoal($goalIds = [])
    {
        foreach ($goalIds as $value) {
            $children[$value] = $this->japaneseLearn_repository->goalNochild([$value]);
        }

        $japaneseLearn = JapaneseLearn::where('user_id', Auth::id())->get();
        $getIds = $japaneseLearn->pluck('goal_id')->toArray();
        $nextGoal = [];
        foreach ($goalIds as $value) {
            $findIdLearn = array_intersect($children[$value], $getIds);
            if ($findIdLearn != []) {
                $JapaneseLearn = JapaneseLearn::whereIn('goal_id', $findIdLearn)->where('user_id',
                    Auth::id())->OrderBy('id', 'desc')->first();
                $nextJapanseseLearn = $this->findNextGoals($JapaneseLearn->goal_id);

                if (isset($nextJapanseseLearn) || isset($prevJapanseseLearn)) {
                    $nextGoal[$value] = $nextJapanseseLearn;
                }
            }
            if (!isset($nextGoal[$value])) {
                $getInfoGoal = $this->findNextGoals(current($children[$value]));
                if (isset($getInfoGoal)) {
                    $nextGoal[$value] = $getInfoGoal;
                }
            }
        }
        return $nextGoal;
    }

    public function findNextGoals($id)
    {
        $japaneseGoal = $this->japaneseGoal_repository->getJapaneseGoal('goal_id', $id)->first();
        $getNameGoal = $this->japaneseGoal_repository->findGoal($id);
        $nextGoal = null;
        if (isset($japaneseGoal)) {
            $nextGoal = [
                'id'   => @$japaneseGoal->goal_id,
                'name' => @$getNameGoal->name,
                'type' => @$japaneseGoal->type
            ];
        }
        return $nextGoal;
    }

    public function countGoals($_, array $args)
    {
        return $this->goal_repository->countGoals($args);
    }

    public function myGoalsAchieve($_, array $args)
    {
        $goals = $this->goal_repository->myGoalsAchieve();
        //NEXT GOAL
        $goalIds = $goals->pluck('id')->toArray();
        $nextGoal = $this->nextGoal($goalIds);
        $goals = $goals->map(function ($goal) use ($nextGoal) {
            $goal->next_goal = @$nextGoal[$goal->id];
            return $goal;
        });
        return $goals;
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
        if (!isset($args['id'])) {
            return false;
        }
//        return $this->goal_repository->goalsAchieveTreeSort($args['id']);
        $achieve = Achieve::where('general_infos.goal_id', $args['id'])
            ->join('general_infos', 'general_infos.id', '=', 'achieves.general_id')
            ->first();
        return $this->goal_repository->getTreeSortByGoalId($achieve->goal_id, $achieve->user_id);
    }

    public function reportGoal($_, array $args)
    {
        return $this->goal_repository->reportGoal($args);
    }

    public function myGoalShare($_, array $args)
    {
        return $this->goal_repository->myGoalShare();
    }
    public function goalShareTreeSort($_, array $args)
    {
        return $this->goal_repository->goalShareTreeSort($args);
    }
}