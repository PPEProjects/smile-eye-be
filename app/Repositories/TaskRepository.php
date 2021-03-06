<?php


namespace App\Repositories;

use App\Models\Task;
use App\Models\Todolist;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

class TaskRepository
{
    private $generalinfo_repository;

    public function __construct(
        GoalRepository $goal_repository,
        GeneralInfoRepository $generalInfo_repository
    ) {
        $this->goal_repository = $goal_repository;
        $this->generalinfo_repository = $generalInfo_repository;
    }

    public function createTask($payload)
    {

        $payload['user_id'] = Auth::id();
        return Task::create($payload);
    }

    public function deleteTask($args)
    {
        if ($args["delete_type"] == "todolist") {

            $taskGeneralinfo = $this->generalinfo_repository->setType('task')
                ->findByTypeId($args['id']);
            if (empty($taskGeneralinfo->repeat)) {
                Task::where('id', $args['id'])->delete();
            }
            $args['status'] = 'delete';
            $data = array_diff_key($args, array_flip(['directive', 'id']));
            $data["user_id"] = Auth::id();
            return (boolean) Todolist::updateOrCreate(
                ['task_id' => @$args['id'], 'checked_at' => $args['checked_at']],
                $data
            );
        } else {
            $args["task_id"] = $args["id"];
            $todo = Todolist::where('task_id', $args["id"])
                ->where('checked_at', "like", $args['checked_at'] . "%")
                ->first();
            if ($todo) {
                $args["status"] = "delete";
                $args = array_diff_key($args, array_flip(['directive', 'id']));
                $todo->update($args);
            }
            $task = Task::where('id', $args['task_id'])->first();
            Todolist::where('task_id', $task->id)
                ->whereNull('name')
                ->update(['name' => $task->name]);
            return $task->delete();
        }
    }

    public function updateTaskAndGeneral($args)
    {
        if (isset($args['general_info']['repeat']) && !in_array($args['general_info']['repeat'],
                [null, 'every day', 'every week', 'every month'])) {
            throw new Error('General Info Repeat invalid');
        }
        $task = $this->updateTask($args);
        $generalInfo = $this->generalinfo_repository
            ->setType('task')
            ->upsert(array_merge($task->toArray(), $args))
            ->findByTypeId($task->id);
        $task->general_info = $generalInfo;
        return $task;
    }

    public function updateTask($payload)
    {
        return tap(Task::findOrFail($payload["id"]))
            ->update($payload);
//        $generalInfo = $this->generalinfo_repository
//            ->setType('task')
//            ->upsert(array_merge($task->toArray(), $args))
//            ->findByTypeId($task->id);
//        $task->general_info = $generalInfo;
//        return $task;
    }

    public function getTasksByGoalId($goalId)
    {
        $tasks = Task::orderBy('id', 'desc');
        $tasks = $tasks->where('goal_id', $goalId);
        $tasks = $tasks->get();
        return $tasks;
    }

//    public function detailTask($id)
//    {
//        return Task::where('id', $id)->first();
////        $task = Task::where('id', $id)->first();
////        if ($task) {
////            $generalInfo = $this->generalinfo_repository
////                ->setType('task')
////                ->findByTypeId($task->id);
////            $task->general_info = $generalInfo;
////            return $task;
////        }
////        return null;
//    }

    public function detailTask(array $args)
    {
        $task = $this->find($args['id']);
        $generalInfo = $this->generalinfo_repository
            ->setType('task')
            ->upsert(array_merge($task->toArray(), $args))
            ->findByTypeId($task->id);
        $task->general_info = $generalInfo;
        return $task;
    }

    public function find($id, $select = '*')
    {
        return Task::selectRaw($select)
            ->where('id', $id)
            ->first();
    }

}