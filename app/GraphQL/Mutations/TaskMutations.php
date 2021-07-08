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
        if (!empty($args['goal_id'])) {
            $checkGoalExists = Task::where('goal_id', $args['goal_id']);
//            if (!empty($args['id'])) {
//                $checkGoalExists = $checkGoalExists->where('id', '!=', $args['id']);
//            }
            if ($checkGoalExists->exists()) {
                throw new Error('This goal already move to the task');
            }
        }
        if (isset($args['general_info']['repeat']) && !in_array($args['general_info']['repeat'],
                [null, 'every day', 'every week', 'every month'])) {
            throw new Error('General Info Repeat invalid');
        }
        if(isset($args['goal_id'])) {
            $checkGoalId = Task::where('goal_id', $args['goal_id'])->first();
            if ($checkGoalId) {
                throw new Error("This goal already add to task ");
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
        if (isset($args['is_change_all']) && $args['is_change_all'] == true){
                $getGoalId = Goal::where('task_id', $args['id'])->first();
                $GoalIdFromTask = $this->checkGoalId($args['id']);
                $update = array_diff_key($args, array_flip(['directive', 'id','is_change_all']));
                if ($getGoalId || $GoalIdFromTask) {
                    if ($getGoalId) {
                        $update['id'] = $getGoalId->id;
                    }
                    if ($GoalIdFromTask) {
                        $update['id'] = $GoalIdFromTask->id;
                    }
                    $updateGoal = tap(Goal::findOrFail($update["id"]))
                        ->update($update);
                    $generalInfo = $this->generalinfo_repository
                        ->setType('goal')
                        ->upsert(array_merge($updateGoal->toArray(), $update))
                        ->findByTypeId($updateGoal->id);
                    $updateGoal->general_info = $generalInfo;
                }
        }
        $task = $this->task_repository->updateTaskAndGeneral($args);
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
    public function checkGoalId($idTask){
        $check = Task::find($idTask);
        if ($check){
            if ($check->goal_id){
                $goalId = $check->goal_id;
                $goal = Task::find($goalId);
                if ($goal) {
                    return $goal;
                }
            }
        }
        return false;
    }
}