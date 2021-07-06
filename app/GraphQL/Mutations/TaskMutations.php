<?php


namespace App\GraphQL\Mutations;

use App\Models\Goal;
use App\Models\Task;
use App\Models\Todolist;
use App\Repositories\GeneralInfoRepository;
use App\Repositories\GoalRepository;
use App\Repositories\TaskRepository;
use GraphQL\Error\Error;

class TaskMutations
{
    private $task_repository;
    private $goal_repository;
    private $generalinfo_repository;

    public function __construct(
        TaskRepository $TaskRepository,
        GoalRepository $GoalRepository,
        GeneralInfoRepository $generalinfo_repository
    ) {
        $this->task_repository = $TaskRepository;
        $this->goal_repository = $GoalRepository;
        $this->generalinfo_repository = $generalinfo_repository;
    }

    public function createTask($_, array $args)
    {
        if (isset($args['general_info']['repeat']) && !in_array($args['general_info']['repeat'],
                [null, 'every day', 'every week', 'every month'])) {
            throw new Error('General Info Repeat invalid');
        }
        if(isset($args['goal_id'])) {
            $checkGoalId = Task::where('goal_id', $args['goal_id'])->first();
            if ($checkGoalId) {
                return;
            }
        }
        $task = $this->task_repository->createTask($args);
        $generalInfo = $this->generalinfo_repository
            ->setType('task')
            ->upsert(array_merge($task->toArray(), $args))
            ->findByTypeId($task->id);
        $task->general_info = $generalInfo;
        return $task;
    }

    public function updateTask($_, array $args)
    {
        if (isset($args['general_info']['repeat']) && !in_array($args['general_info']['repeat'],
                [null, 'every day', 'every week', 'every month'])) {
            throw new Error('General Info Repeat invalid');
        }
        $task = $this->task_repository->updateTask($args);
        $generalInfo = $this->generalinfo_repository
            ->setType('task')
            ->upsert(array_merge($task->toArray(), $args))
            ->findByTypeId($task->id);
        $task->general_info = $generalInfo;
        return $task;
    }


    public function deleteTask($_, array $args): bool
    {
        return $this->task_repository->deleteTask($args);

    }

    public function goalToTask($_, array $args)
    {
        $args['parent_id'] = $args['goal_id'];
        $goalsDt = $this->goal_repository->getDetailGoal($args['goal_id']);
        $task_id = $goalsDt->toArray()['task_id'];
        if ($task_id == null) {
            $goals = $this->goal_repository->getChildrenGoals($args['goal_id']);
            $tasks = $this->task_repository->getTasksByGoalId($args['goal_id']);
            if (count($goals) == 0 && count($tasks) == 0) {
                return $this->task_repository->createTask($args);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

}