<?php

namespace App\GraphQL\Mutations;

use App\Models\GeneralInfo;
use App\Models\Task;
use App\Models\Todolist;
use App\Repositories\GeneralInfoRepository;
use App\Repositories\GoalRepository;
use App\Repositories\TodolistRepository;
use Carbon\Carbon;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;
use App\Repositories\TaskRepository;


class TodolistMutations
{

    private $todolist_repository;
    private $goal_repository;
    private $generalinfo_repository;
    private $task_repository;

    public function __construct(
        GeneralInfoRepository $generalinfo_repository,
        TodolistRepository $TodolistRepository,
        GoalRepository $GoalRepository,
        TaskRepository $task_repository
    ) {
        $this->generalinfo_repository = $generalinfo_repository;
        $this->todolist_repository = $TodolistRepository;
        $this->goal_repository = $GoalRepository;
        $this->task_repository = $task_repository ;
    }

    public function createTodolist($_, array $args): Todolist
    {
//        return $this->todolist_repository->createTodolist($args);
        $args['user_id'] = Auth::id();
        $create = Todolist::create($args);
        return $create;
    }

    public function deleteTodolist1($_, array $args): Bool
    {
        return $this->todolist_repository->deleteTodolist($args);
    }

// Ver 2
    protected function _updateOrCreate($args)
    {
        $task = Task::where("id",$args["task_id"])->first();
        if (isset($args["general_info"]["id"]))
            $args["general_info"] = array_diff_key($args["general_info"],array_flip(["id"]));
        if (!isset($args["name"])){
            $args = array_diff_key($args,array_flip(["name"]));
        }
        if (isset($args["status"])){
            if ($args["status"] != "todo" && $args["status"] != "done")
            throw new Error('Status invalid');
        }
        else $args = array_diff_key($args,array_flip(["status"]));
        $args['user_id'] = Auth::id();
        $goal = $this->goal_repository->findByTaskId($args['task_id']);
        $args['goal_id'] = @$goal->id;
        $args['checked_at'] = $args['checked_at'];
        $args['checked_at'] =  date('Y-m-d 00:00:00', strtotime($args['checked_at']));
        $generalTask = GeneralInfo::where("task_id",$args['task_id'])->first();
        $todolist = Todolist::updateOrCreate(
            ['task_id' => $args['task_id'], 'checked_at' => $args['checked_at']],
            $args
        );

        if (isset($args["general_info"]["color_change"])){
            if ($args["general_info"]["color_change"] == "all") {
                $args1["id"] = $args["task_id"];
                $args1["general_info"] = array_intersect_key($args["general_info"], array_flip(["color"]));
                $this->task_repository->updateTaskAndGeneral($args1);
            }
        }
        if (isset($args["edit_type"])){
            switch ($args["edit_type"]){
                case "all":
                    if($task) {
                        $args1 = $args;
                        $args1["id"] = $args["task_id"];
                        $this->task_repository->updateTaskAndGeneral($args1);
                    }
                    break;
            }
        }

        $args["id"] = $todolist->id;
        if (!isset($args["general_info"]["color"])){
            $args["general_info"]["color"] = $generalTask->color;
        }

        $generalInfo = $this->generalinfo_repository
            ->setType('todolist')
            ->upsert(array_merge($todolist->toArray(), $args))
            ->findByTypeId($todolist->id);
        $todolist->general_info = $generalInfo;
        return $todolist;
    }

    public function upsertTodolist($_, array $args)
    {
        return $this->_updateOrCreate($args);
    }

    public function deleteTodolist($_, array $args): Todolist
    {
        $args['status'] = 'delete';
        return $this->_updateOrCreate($args);
    }

    public function updateTodolist($_, array $args): Todolist
    {
        $todolist = $this->todolist_repository->updateTodolist($args);
        $generalInfo = $this->generalinfo_repository
            ->setType('todolist')
            ->upsert(array_merge($todolist->toArray(), $args))
            ->findByTypeId($todolist->id);
        $todolist->general_info = $generalInfo;
        return $todolist;
    }
}