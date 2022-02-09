<?php

namespace App\Repositories;

use App\Models\GeneralInfo;
use App\Models\Goal;
use App\Models\Task;
use App\Models\Todolist;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\AttachmentService;
use App\Repositories\GeneralInfoRepository;
use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class TodolistRepository
{
    private $attachment_service;
    private $user_repository;
    private $goal_repository;
    private $generalinfo_repository;

    public function __construct(
        UserRepository        $UserRepository,
        GoalRepository        $GoalRepository,
        AttachmentService     $AttachmentService,
        TaskRepository        $TaskRepository,
        GeneralInfoRepository $generalinfo_repository
    )
    {
        $this->user_repository = $UserRepository;
        $this->attachment_service = $AttachmentService;
        $this->goal_repository = $GoalRepository;
        $this->task_repository = $TaskRepository;
        $this->generalinfo_repository = $generalinfo_repository;
    }

    public function createTodolist($args)
    {
        $args['user_id'] = Auth::id();
        $create = Todolist::create($args);
        return $create;
    }

    public function updateTodolist($args)
    {
//        $args['id'] = $goalIds[0]['id'];
        $args = array_diff_key($args, array_flip(["directive"]));
        $update = tap(Todolist::findOrFail($args['id']))
            ->update($args);

        return $update;
    }

    public function myTodolist($args)
    {
        $args['user_id'] = Auth::id();
        $compie = null;
        $task = Task::where('user_id', $args['user_id'])
            ->get();
        $idTask = $task->pluck('id');
        $todolist = Todolist::where('user_id', $args['user_id'])
            ->get();
        $id_todolist = $todolist->pluck('id');
        $general_everyday = GeneralInfo::where('user_id', $args['user_id'])
            ->where('repeat', 'every day')
            ->where('created_at', '<=', $args['created_at'] . " 23:59:59")
            ->whereIn('task_id', $idTask)
            ->Orwhere('todolist_id', $id_todolist)->get();
//        dd($general_everyday->toArray());
        $result_everyday = $general_everyday;

        $week = date_create($args['created_at']);
        $week = date_format($week, 'l');
        $general_everyweek = GeneralInfo::where('user_id', $args['user_id'])
            ->where('repeat', 'every week')
            ->whereIn('task_id', $idTask)
            ->get();
        $compie = $result_everyday;

        $result_week = $general_everyweek->map(function ($evey_week) use ($week) {
            $day = date_create($evey_week->created_at);
            $day = date_format($day, 'l');
            if ($week == $day) {
                return $evey_week;
            }
        });
        if ($general_everyweek->toArray() != []) {
            if ($result_week->toArray()[0] != null) {
                $compie = $result_week->merge($compie);
            }
        }
        $month = date_create($args['created_at']);
        $month = date_format($month, 'j');
        $general_everymonth = GeneralInfo::where('user_id', $args['user_id'])
            ->where('repeat', 'every month')
            ->whereIn('task_id', $idTask)
            ->get();

        $result_month = $general_everymonth->map(function ($every_month) use ($month) {
            $day = date_create($every_month->created_at);
            $day = date_format($day, 'j');
            if ($month == $day) {
                return $every_month;
            }
        });
        if ($general_everymonth->toArray() != []) {
            if ($result_month->toArray()[0] != null) {
                $compie = $result_month->merge($compie);
            }
        }

        $myTaskTodo = $compie;

        return $myTaskTodo;
    }

    public function find($id)
    {
        return Todolist::find($id);
    }

    public function my_todolists_with_month($args)
    {
//        if ($args['created_at'] != "") {
            $todolists = Todolist::where('created_at', 'like', $args['created_at'] . "%")
                ->where('status', 'not LIKE', 'delete')
                ->where('user_id', $args['user_id'])
                ->orderBy('created_at', 'ASC')
                ->get();
            $newArray = [];
            $newArray = $todolists->map(function ($todolist) {
                $create_at = date('Y-m-d', strtotime($todolist['created_at']));
                return $create_at;
            });
//dd($newArray);
            $workload = 1;
            $a = [];
            $check = "";
            $number = 0;
            foreach ($newArray as $key => $value) {
                if (isset($a[$key - 1]['date'])) {
                    $check = $a[$key - 1]['date'];
                    $number = $key - 1;
                }
                if ($value == $check) {
                    $a[$number]['workload'] = $a[$number]['workload'] + 1;
                } else {
                    $a[$key]['date'] = $value;
                    $a[$key]['workload'] = $workload;
                }
            }
            return $a;
//        } else {
//            return "input not null";
//        }
    }

    public function deleteTodolist($args)
    {

//        $delete = $this->updateTodolist($args);
//        if ($delete){
//            return true;
//        }
//        return false;
        $args = array_diff_key($args, array_flip(['directive']));
        $args['status'] = 'delete';
        $update = tap(Todolist::findOrFail($args["id"]))
            ->update($args);
        $delete = Todolist::find($args['id']);
        if ($delete) {
            return $delete->delete();
        } else {
            return false;
        }
    }

    public function detailTodolist($args)
    {
        $todolist = Todolist::where('id', $args['id'])->first();
        if ($todolist) {
            $generalInfo = $this->generalinfo_repository
                ->setType('todolist')
                ->findByTypeId($todolist->id);
            $todolist->general_info = $generalInfo;
            return $todolist;
        }
        return null;
    }

}
