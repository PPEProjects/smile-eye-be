<?php

namespace App\GraphQL\Queries;

use App\Models\GeneralInfo;
use App\Models\Task;
use App\Models\Todolist;
use App\Repositories\GeneralInfoRepository;
use App\Repositories\TodolistRepository;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TodoListQueries
{
    private $todolist_repository;
    private $generalinfo_repository;

    public function __construct(
        TodolistRepository $TodolistRepository,
        GeneralInfoRepository $generalinfo_repository
    ) {
        $this->todolist_repository = $TodolistRepository;
        $this->generalinfo_repository = $generalinfo_repository;
    }

    public function myTodoLists1($_, array $args)
    {
//        return $this->todolist_repository->myTodolist($args);
        $checkedAt = $args['checked_at'];
        $args['user_id'] = Auth::id();
        $task = Task::where('user_id', Auth::id())
            ->whereRaw("action_at LIKE '%$checkedAt' OR ")
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

    public function my_todolists_with_month($_, array $args)
    {
        $args['user_id'] = Auth::id();
        return $this->todolist_repository->my_todolists_with_month($args);
    }

// Ver 2

    public function myTodoLists($_, array $args)
    {
        $checkedAt = $args['checked_at'];
        $query = "
SELECT tasks.id as id
FROM tasks
    left join general_infos gi on tasks.id = gi.task_id
WHERE
      action_at IS NULL AND DATE(tasks.created_at) = DATE('$checkedAt') OR DATE(action_at) <= DATE('$checkedAt') AND
      (`repeat` IS NULL AND DATE(action_at) = DATE('$checkedAt')
   OR `repeat` = 'every day'
   OR `repeat` = 'every week' AND WEEKDAY(action_at) = WEEKDAY('$checkedAt')
   OR `repeat` = 'every month' AND DAYOFMONTH(action_at) = DAYOFMONTH('$checkedAt'))";
        $results = DB::select(DB::raw($query));
        $res = json_decode(json_encode($results), true);
        $taskIds = array_map(function ($item) {
            return $item['id'];
        }, $res ?? []);

        $todolists = Todolist::selectRaw("*, id as todolist_id, 'todolist' as type")
            ->where('user_id', Auth::id())
            ->whereRaw("DATE(checked_at) = DATE('$checkedAt')")
            ->orderBy('status', 'desc')
            ->get();
        $todolists = $this->generalinfo_repository
            ->setType('todolist')
            ->get($todolists);
        $tasks = Task::selectRaw("*, id as task_id, 'task' as type")
            ->where('user_id', Auth::id())
            ->whereIn('id', $taskIds)
            ->get();
//            ->map(function ($task) use (&$todolists) {
//                $todolist = $todolists->where('task_id', $task->id)->first();
//                $task->status = @$todolist->status;
//                $todolists = $todolists->where('task_id', '!=', $task->id);
//                return $task;
//            });
        $tasks = $this->generalinfo_repository
            ->setType('task')
            ->get($tasks);
        $todolists = $todolists->map(function ($todolist) use (&$tasks) {
            $task = $tasks->where('task_id', $todolist->task_id)->first();
            if ($task) {
                $todolist->name = empty($todolist->name) ? $task->name : $todolist->name;
                $todolist->task = $task;
                $tasks = $tasks->where('task_id', '!=', $task->id);
            }
            return $todolist;
        });

        $tasks = $tasks->concat($todolists)
                ->where('status', '!=', 'delete');
        $sortBy = "default";
        if (isset($args['sort_by'])){
            $sortBy = $args['sort_by'];
        }
        switch ($sortBy) {
            case 'by time':
                $tasks = $tasks->sortByDESC('task_id');
                $actionAtNoNull = $tasks->WhereNotNull('general_info.action_at')->sortBy('general_info.action_at');
                $actionAtNull = $tasks->WhereNull('general_info.action_at');
                $tasks = $actionAtNoNull->concat($actionAtNull);
                break;
            case 'by done':
                $tasks = $tasks->sortBy('-`status`');
                break;
            default:
                $actionAtNull = $tasks->WhereNull('general_info.action_at')->sortBy(['general_info.action_at', 'ASC']);
                $actionAtNoNull = $tasks->WhereNotNull('general_info.action_at')->sortByDESC('task_id');
            $tasks = $tasks = $actionAtNull->concat($actionAtNoNull);
        }

        return $tasks;
    }

    public function detailTodolist($_, array $args)
    {
//        return $this->todolist_repository->detailTodolist($args);
//        $todolist = Todolist::where('id', $args['id'])->first();
//        if ($todolist) {
//            $generalInfo = $this->generalinfo_repository
//                ->setType('todolist')
//                ->findByTypeId($todolist->id);
//            $todolist->general_info = $generalInfo;
//            return $todolist;
//        }
        if (isset($args['id'])) {
            $todolist = Todolist::where('id', $args['id'])->first();
        }
        if (isset($args['task_id'], $args['checked_at'])) {
            $todolist = Todolist::where('task_id', $args['task_id'])
                ->whereRaw("DATE(checked_at) = DATE('{$args['checked_at']}')")
                ->first();
        }
        if ($todolist) {
            $generalInfo = $this->generalinfo_repository
                ->setType('todolist')
                ->findByTypeId($todolist->id);
            $todolist->general_info = $generalInfo;
            return $todolist;
        }
        return null;
    }


    public function myTodolistsFromNow($_, array $args)
    {
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now()->addDays(30);

        $query = "SELECT tasks.id, DATE(IFNULL(gi.action_at, tasks.created_at)) as action_at
FROM tasks
     left join general_infos gi on tasks.id = gi.task_id
WHERE tasks.user_id=".Auth::id()." AND tasks.deleted_at IS NULL AND gi.`repeat` is NULL AND (action_at IS NULL AND DATE(tasks.created_at) BETWEEN '$startDate' AND '$endDate'
   OR DATE(action_at) BETWEEN '$startDate' AND '$endDate')";
        $results = DB::select(DB::raw($query));
        $tasksAction = json_decode(json_encode($results), true);
        $tasksAction = array_map(function ($item) {
            return $item['action_at'];
        }, $tasksAction);
        $tasksAction = array_count_values($tasksAction);

        $query = "SELECT tasks.id, DATE(IFNULL(gi.action_at, tasks.created_at)) as action_at
FROM tasks
     inner join general_infos gi on tasks.id = gi.task_id
WHERE tasks.user_id=".Auth::id()." AND tasks.deleted_at IS NULL AND gi.`repeat`='every day'";
        $results = DB::select(DB::raw($query));
        $tasksEveryDay = json_decode(json_encode($results), true);
        $tasksEveryDay = array_map(function ($item) {
            return $item['action_at'];
        }, $tasksEveryDay);

        $workloads = [];
        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            $date = $date->format('Y-m-d');
            $workload = [
                'date'     => $date,
                'workload' => (@$tasksAction[$date] ?? 0) + count($tasksEveryDay)
            ];
            if (!empty($workload['workload'])) {
                $workloads[] = $workload;
            }
        }
        return $workloads;
    }
}