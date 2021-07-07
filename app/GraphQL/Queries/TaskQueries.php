<?php


namespace App\GraphQL\Queries;

use App\Models\Task;
use App\Repositories\GeneralInfoRepository;
use App\Repositories\TaskRepository;
use Illuminate\Support\Facades\Auth;

class TaskQueries
{
    private $generalinfo_repository;
    private $task_repository;

    public function __construct(GeneralInfoRepository $generalinfo_repository, TaskRepository $task_repository)
    {
        $this->generalinfo_repository = $generalinfo_repository;
        $this->task_repository = $task_repository;
    }

    public function my_tasks($_, array $args)
    {
//        $cons = array_merge(['user_id' => Auth::id()], $args);

        $args['user_id'] = Auth::id();
        $myTask = Task::where('user_id', $args['user_id'])
            ->where('created_at', 'like', $args['created_at'] . "%")
            ->get();

        return $myTask;
    }

    public function detailTask($_, array $args)
    {
//        return $this->task_repository->detailTask($args);
        $task = Task::find($args['id']);
        if(!$task){
            return ;
        }
        $generalInfo = $this->generalinfo_repository
            ->setType('task')
            ->upsert(array_merge($task->toArray(), $args))
            ->findByTypeId($task->id);
        $task->general_info = $generalInfo;
        return $task;
    }

    public function myTasks($_, array $args)
    {
        $cons = array_merge(['user_id' => Auth::id()], $args);
        $tasks = Task::orderBy('id', 'desc');
        $tasks = $tasks->get();
        $tasks = $this->generalinfo_repository
            ->setType('task')
            ->get($tasks);
        return $tasks;
    }
}