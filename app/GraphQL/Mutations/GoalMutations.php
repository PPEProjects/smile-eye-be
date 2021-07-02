<?php

namespace App\GraphQL\Mutations;

use App\Models\Goal;
use App\Models\Task;
use App\Models\Todolist;
use App\Repositories\AttachmentRepository;
use App\Repositories\GeneralInfoRepository;
use App\Repositories\GoalRepository;
use App\Repositories\TaskRepository;
use Carbon\Carbon;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\AttachmentService;

class GoalMutations
{
    private $general_info_repository;
    private $generalinfo_repository;
    private $attachment_repository;
    private $goal_repository;
    private $task_repository;
    private $attachment_service;

    public function __construct(
        GeneralInfoRepository $general_info_repository,
        GoalRepository $GoalRepository,
        TaskRepository $TaskRepository,
        AttachmentService $attachment_service,
        GeneralInfoRepository $generalinfo_repository,
        AttachmentRepository $attachment_repository
    ) {
        $this->general_info_repository = $general_info_repository;
        $this->attachment_service = $attachment_service;
        $this->generalinfo_repository = $generalinfo_repository;
        $this->attachment_repository = $attachment_repository;
        $this->goal_repository = $GoalRepository;
        $this->task_repository = $TaskRepository;
    }

    public function createGoal($_, array $args): Goal
    {
        if (isset($args['start_day'], $args['end_day'])) {
            $startDay = Carbon::createFromFormat('Y-m-d H:i:s', $args['start_day']);
            $endDay = Carbon::createFromFormat('Y-m-d H:i:s', $args['end_day']);
            if (!$startDay->lte($endDay)) {
                throw new Error('Start day must less than end day');
            }
        }

        $args['user_id'] = Auth::id();
        $goal = Goal::create($args);
        $generalInfo = $this->generalinfo_repository
            ->setType('goal')
            ->upsert(array_merge($goal->toArray(), $args))
            ->findByTypeId($goal->id);
        $goal->general_info = $generalInfo;
        return $goal;
    }

    public function deleteGoal($_, array $args): bool
    {
//        $goal = Goal::find($args['id']);
        $args = array_diff_key($args, array_flip(['directive']));
        $args['status'] = 'delete';
        $update = tap(Goal::findOrFail($args["id"]))
            ->update($args);
        $deleteGoal = Goal::find($args['id']);
        return $deleteGoal->delete();
    }

    public function taskToGoal($_, array $args)
    {
        $startDay = Carbon::createFromFormat('Y-m-d H:i:s', $args['start_day']);
        $endDay = Carbon::createFromFormat('Y-m-d H:i:s', $args['end_day']);
        if (!$startDay->lte($endDay)) {
            throw new Error('Start day must less than end day');
        }
        $taskDt = $this->task_repository->find($args['task_id']);
        $goal_id = $taskDt->toArray()['goal_id'];
        if ($goal_id == null) {
            $goals = $this->goal_repository->getGoalsByTaskId($args['task_id']);
            if (count($goals) == 0) {
                $args['user_id'] = Auth::id();
                $goal = Goal::create($args);
                return $goal;
            } else {
                return null;
            }
        } else {
            return null;
        }

    }

// Ver 2
    public function upsertGoal($_, array $args)
    {
        \Illuminate\Support\Facades\Log::channel('single')->info('$args', [$args]);
        
        if (isset($args['start_day'], $args['end_day'])) {
            $startDay = Carbon::createFromFormat('Y-m-d H:i:s', $args['start_day']);
            $endDay = Carbon::createFromFormat('Y-m-d H:i:s', $args['end_day']);
            if (!$startDay->lte($endDay)) {
                throw new Error('Start day must less than end day');
            }
        }
        $args['user_id'] = Auth::id();
        $goal = Goal::updateOrCreate(
            ['id' => @$args['id']],
            $args
        );
        $this->generalinfo_repository
            ->setType('goal')
            ->upsert(array_merge($goal->toArray(), $args));

        return $this->goal_repository->getTreeSortByGoalId($args['root_id'], Auth::id());
    }

    public function updateGoal($_, array $args): Goal
    {
        if (isset($args['start_day'], $args['end_day'])) {
            $startDay = Carbon::createFromFormat('Y-m-d H:i:s', $args['start_day']);
            $endDay = Carbon::createFromFormat('Y-m-d H:i:s', $args['end_day']);
            if (!$startDay->lte($endDay)) {
                throw new Error('Start day must less than end day');
            }
        }

        $goal = tap(Goal::findOrFail($args["id"]))
            ->update($args);
        $generalInfo = $this->generalinfo_repository
            ->setType('goal')
            ->upsert(array_merge($goal->toArray(), $args))
            ->findByTypeId($goal->id);
        $goal->general_info = $generalInfo;

//        $this->goal_repository->calculatorProcessUpdate($goal);
        return $goal;
    }

}