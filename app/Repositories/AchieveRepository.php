<?php

namespace App\Repositories;

use App\Models\Achieve;
use App\Models\Attachment;
use App\Models\GeneralInfo;
use App\Models\Goal;
use App\Models\Notification;
use App\Models\Task;
use App\Models\Todolist;
use App\Models\User;
use Carbon\Carbon;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\AttachmentService;
use App\Repositories\TodolistRepository;

class AchieveRepository
{
    private $notification_repository;
    private $generalinfo_repository;
    private $task_repository;
    private $attachment_service;

    public function __construct(
        TaskRepository $TaskRepository,
        NotificationRepository $notification_repository,
        GeneralInfoRepository $generalinfo_repository,
        AttachmentService $attachment_service,
        TodolistRepository $todolist_repository
    ) {
        $this->task_repository = $TaskRepository;
        $this->generalinfo_repository = $generalinfo_repository;
        $this->notification_repository = $notification_repository;
        $this->attachment_service = $attachment_service;
        $this->todolist_repository = $todolist_repository;
    }
    public function createAchieve($args)
    {
        $queryAchieves = Achieve::Where("general_id", $args["general_id"])->get();
        $UserInviteIds = $queryAchieves->pluck("user_invite_id")->toArray();
        $args['user_id'] = Auth::id();
        if ($UserInviteIds != []){
            $args['user_invite_ids'] = array_diff( $args["user_invite_ids"], $UserInviteIds);
        }
        $achives = collect();
        if ($args['user_invite_ids'] == []){
            return  $achives->toArray();
        }
         foreach ($args['user_invite_ids'] as $single){
             $tam = $args;
             $tam['user_invite_id'] = $single;
             $achive = Achieve::create($tam);
             $achives->push($achive);

         }
         return $achives->toArray();
    }
    public function updateAchieve($args)
    {
        $args = array_diff_key($args, array_flip(['directive']));
        $args['user_id'] = Auth::id();
        $update = tap(Achieve::findOrFail($args["id"]))
            ->update($args);
        return $update;

    }
    public function detailAchieve($args){
        $achieve = Achieve::where("general_id",$args['general_id'])
            ->first();
        $user = User::find($achieve->user_id);
        $user = $this->attachment_service->mappingAvatarBackgroud($user);
        $achieve->user = $user;
            $general_id = $achieve->general_id;
            $general = GeneralInfo::where("id",$general_id)->get();
            $general = $this->attachment_service->mappingAttachments($general)->first();

            $user_invite = Achieve::where('user_id',$achieve->user_id)
                ->where('general_id',$general_id)
                ->pluck("user_invite_id");
            //even me
            $member = User::whereIn('id',$user_invite)->get();

            $day = null;
            $todolist = collect();
            //add process if type = goal
            if ($general["goal_id"]){
                $goal = Goal::where("id",$general["goal_id"])->first();
                $goal->general_info = $general;
                $achieve->detail = $goal;

                $star_day = Carbon::parse($goal->start_day);
                $end_day = Carbon::parse($goal->end_day);

                $day = $end_day->diffInDays($star_day) + 1;

                $todolist = Todolist::selectRaw("user_id, count(*) as count")
                    ->where("goal_id", $general["goal_id"])
                    ->groupBy("user_id")
                    ->get();
                $member= $member->map(function ($user) use ($todolist,$day){
                    //map process
                    if ($day > 0){
                        $count = $todolist->where('user_id',$user->id)->first() ;
                        if($count){
                            $count = $count->count;
                            $process = $count / $day *100;
                        }else{
                            $process =0;
                        };
                        $user->process = $process;
                    }else if (is_null($day)){
                        $user->process = null ;
                    }else{
                        $user->process = 100 ;
                    }
                    //map attachment,detail and process

                    $attachment = Attachment::where('id',$user->avatar_attachment_id)->first();
                    if ($attachment) {
                        [$thumb, $file] = $this->attachment_service->getThumbFile($attachment->file_type, $attachment->file);
                        $attachment->thumb = $thumb;
                        $attachment->file = $file;
                    }
                    $user->attachment = $attachment;
                    return $user;
                });

            }else if($general["task_id"]){
                $task = Task::where("id",$general["task_id"])->first();
                $task->general_info = $general;
                $achieve->detail = $task;
                $member= $member->map(function ($user) {
                    //map attachment,detail and process
                    $attachment = Attachment::where('id',$user->avatar_attachment_id)->first();
                    if ($attachment) {
                        [$thumb, $file] = $this->attachment_service->getThumbFile($attachment->file_type, $attachment->file);
                        $attachment->thumb = $thumb;
                        $attachment->file = $file;
                    }
                    $user->attachment = $attachment;
                    return $user;
                });
            }else if($general["todolist_id"]){
                $todolist = Todolist::where("id",$general["todolist_id"])->first();
                $todolist->general_info = $general;
                $achieve->detail = $todolist;
                $member= $member->map(function ($user) {
                    //map attachment,detail and process
                    $attachment = Attachment::where('id',$user->avatar_attachment_id)->first();
                    if ($attachment) {
                        [$thumb, $file] = $this->attachment_service->getThumbFile($attachment->file_type, $attachment->file);
                        $attachment->thumb = $thumb;
                        $attachment->file = $file;
                    }
                    $user->attachment = $attachment;
                    return $user;
                });
            }
            $achieve->member = $member;
            return $achieve;
    }
    public function updateAchieveWithGeneralId($args){

        $achieve = Achieve::where("general_id",$args["general_id"])
            ->where("user_invite_id",Auth::id())
            ->first();
        if(isset($args['status']) && $args['status'] == "accept" ){
            $general = GeneralInfo::find($args['general_id']);
            if(isset($general->task_id)){
                 $task = Task::find($general->task_id);           
                 $createTask = Task::create([
                                            "name" => $task->name,
                                            "user_id" => Auth::id()
                                            ]);
                  $this->generalinfo_repository
                                     ->setType('task')
                                    ->upsert(array_merge($createTask->toArray(), $args))
                                    ->findByTypeId($createTask->id);
            }
        }
        $args = array_diff_key($args,array_flip(["directive","general_id"]));
        $achieve->update($args);
        return $achieve;
    }
}
