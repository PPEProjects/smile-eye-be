<?php

namespace App\GraphQL\Mutations;

use App\Models\GeneralInfo;
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
use App\Repositories\NotificationRepository;

class GoalMutations
{
    private $general_info_repository;
    private $generalinfo_repository;
    private $attachment_repository;
    private $goal_repository;
    private $task_repository;
    private $attachment_service;
    private $notification_repository;

    public function __construct(
        GeneralInfoRepository $general_info_repository,
        GoalRepository $GoalRepository,
        TaskRepository $TaskRepository,
        AttachmentService $attachment_service,
        GeneralInfoRepository $generalinfo_repository,
        AttachmentRepository $attachment_repository,
        NotificationRepository $notification_repository
    ) {
        $this->general_info_repository = $general_info_repository;
        $this->attachment_service = $attachment_service;
        $this->generalinfo_repository = $generalinfo_repository;
        $this->attachment_repository = $attachment_repository;
        $this->goal_repository = $GoalRepository;
        $this->task_repository = $TaskRepository;
        $this->notification_repository = $notification_repository;
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
        $checkIdTask = $this->checkTaskId($args['parent_id']);
        if(!$checkIdTask){
            return $checkIdTask;
        }
        $goal = Goal::updateOrCreate(
            ['id' => @$args['id']],
            $args
        );
        $this->generalinfo_repository
            ->setType('goal')
            ->upsert(array_merge($goal->toArray(), $args));

        return $this->goal_repository->getTreeSortByGoalId($args['root_id'], Auth::id());
    }

   public function compare_goal($goalnew,$goalOld){
        $generalNew = $goalnew["general_info"];
        $generalOld = $goalOld->general_info;
//        $generalOld->achieves->toArray();
//        $generalOld->publishs->toArray();
        dd(array_diff($generalNew,$generalOld));


    }
    public function updateGoal($_, array $args)
    {
        if (isset($args['start_day'], $args['end_day'])) {
            $startDay = Carbon::createFromFormat('Y-m-d H:i:s', $args['start_day']);
            $endDay = Carbon::createFromFormat('Y-m-d H:i:s', $args['end_day']);
            if (!$startDay->lte($endDay)) {
                throw new Error('Start day must less than end day');
            }
        }
        if (isset($args['parent_id'])) {
            $checkIdTask = $this->checkTaskId($args['parent_id']);
            if (!$checkIdTask) {
                return;
            }
        }

        //self update
        $goalCheckUser = Goal::where("id",$args["id"])->first();

        $generalInfo = $this->generalinfo_repository
            ->setType('goal')
            ->findByTypeId($goalCheckUser->id)
            ->toArray();
        $goalCheckUser->general_info = $generalInfo;
//        dd($goalCheckUser->toArray());
        if (@$goalCheckUser->user_id == Auth::id()){
            $goal = tap(Goal::findOrFail($args["id"]))
                ->update($args);
            $generalInfo = $this->generalinfo_repository
                ->setType('goal')
                ->upsert(array_merge($goal->toArray(), $args))
                ->findByTypeId($goal->id);
            $goal->general_info = $generalInfo;
        }else{
            $goal = Goal::where("id",$args["id"])->first();
            $goalOld = Goal::where("id",$args["id"])->first();

            $goal->update($args);

            $generalInfo = GeneralInfo::where("goal_id",$args["id"])->first();
            $generalInfoOld = GeneralInfo::where("goal_id",$args["id"])->first();

            $generalInfo->update($args["general_info"]);
//            GeneralInfo::where("goal_id",$args["id"])->update($args["general_info"]);
            $goalChange = array_diff_key($goal->getChanges(),array_flip(["updated_at","is_pined"]));
            $generalInfoChange = array_diff_key($generalInfo->getChanges(),array_flip(["updated_at","todolist_id"]));

            //filler information change save as an array
            $temp = collect();
            foreach ($goalChange as $key=>$value){
                $arr[$key]["old"] =  $goalOld->$key;
                $arr[$key]["new"] =  $goalChange[$key];
                $temp->push($arr);
            }
            foreach ($generalInfoChange as $key=>$value){
                $arr[$key]["old"] =  $generalInfoOld->$key;
                $arr[$key]["new"] =  $generalInfoChange[$key];
                $temp->push($arr);
            }
            $temp = @$temp->toArray()[1];
            if ($temp){
                foreach ($temp as $key=>$t){
                    $t2 = [];
                    $t2[$key] = $t;
                    $this->
                    notification_repository->
                    saveNotification("edit_goal",$goal->id,$t2);

                }
            }

            $generalInfo = $this->generalinfo_repository
                ->setType('goal')
                ->findByTypeId($goal->id);
            $goal->general_info = $generalInfo;
        }
        return $goal;
    }
    public function checkTaskId($idGoal){
        $check = Goal::find($idGoal);
        if ($check){
            if ($check->task_id){
                $taskId = $check->task_id;
                $task = Task::find($taskId);
                if ($task) {
                    return false;
                }
            }
        }
        return true;
    }

}