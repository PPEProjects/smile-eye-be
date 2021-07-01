<?php

namespace App\GraphQL\Mutations;

use App\Models\Todolist;
use App\Repositories\GeneralInfoRepository;
use App\Repositories\GoalRepository;
use App\Repositories\TodolistRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TodolistMutations
{

    private $todolist_repository;
    private $goal_repository;
    private $generalinfo_repository;

    public function __construct(
        GeneralInfoRepository $generalinfo_repository,
        TodolistRepository $TodolistRepository,
        GoalRepository $GoalRepository
    ) {
        $this->generalinfo_repository = $generalinfo_repository;
        $this->todolist_repository = $TodolistRepository;
        $this->goal_repository = $GoalRepository;
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
        $args['user_id'] = Auth::id();
        $goal = $this->goal_repository->findByTaskId($args['task_id']);
        $args['goal_id'] = @$goal->id;
        $args['checked_at'] = $args['checked_at'];
        $args['checked_at'] =  date('Y-m-d 00:00:00', strtotime($args['checked_at']));

        $todolist = Todolist::updateOrCreate(
            ['task_id' => $args['task_id'], 'checked_at' => $args['checked_at']],
            $args
        );
        $generalInfo = $this->generalinfo_repository
            ->setType('todolist')
            ->upsert(array_merge($todolist->toArray(), $args))
            ->findByTypeId($todolist->id);
        $todolist->general_info = $generalInfo;
        return $todolist;
    }

    public function upsertTodolist($_, array $args): Todolist
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