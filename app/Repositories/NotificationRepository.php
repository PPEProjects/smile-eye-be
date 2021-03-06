<?php

namespace App\Repositories;

use App\Events\NotificationMessage;
use App\Models\Achieve;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Friend;
use App\Models\GeneralInfo;
use App\Models\Goal;
use App\Models\Notification;
use App\Models\PublishInfo;
use App\Models\Task;
use App\Models\Todolist;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\AttachmentService;
use App\Repositories\TodolistRepository;
use App\Repositories\PublishInfoRepository;

class NotificationRepository
{
    private $task_repository;
    private $goal_repository;
    private $generalinfo_repository;
    private $attachment_service;
    private $comment_repository;
    private $todolist_repository;
    private $publish_info_repository ;

    public function __construct(
        TaskRepository $task_repository,
        GoalRepository $goal_repository,
        GeneralInfoRepository $generalinfo_repository,
        AttachmentService $attachment_service,
        CommentRepository $comment_repository,
        TodolistRepository $todolist_repository,
        PublishInfoRepository $publish_info_repository
    ) {
        $this->task_repository = $task_repository;
        $this->goal_repository = $goal_repository;
        $this->generalinfo_repository = $generalinfo_repository;
        $this->attachment_service = $attachment_service;
        $this->comment_repository = $comment_repository;
        $this->todolist_repository = $todolist_repository;
        $this->publish_info_repository = $publish_info_repository;
    }

    public function detailNotifications(array $args)
    {
        $notifications = Notification::where("id",$args["id"])->get();

        //map user and avatar
        $notifications = $notifications->map(function ($noti){
            $type = $noti->type ;
            $general_id = $noti->content["general_id"];
            switch ($type){
                case 'achieve' :
                    $general = GeneralInfo::where("id",$general_id)->first()->toArray();
                    $day = 0;
                    $todolist = collect();
                    //add process if type = goal
                    if ($general["goal_id"]){
                        $goal = Goal::where("id",$general["goal_id"])->first();

                        $star_day = Carbon::parse($goal->start_day);
                        $end_day = Carbon::parse($goal->end_day);

                        $day = $end_day->diffInDays($star_day) + 1;

                        $todolist = Todolist::selectRaw("user_id, count(*) as count")
                            ->where("goal_id", $general["goal_id"])
                            ->groupBy("user_id")
                            ->get();

                    }
                    $achives = Achieve::where('user_id',$noti->user_id)
                        ->where('general_id',$general_id)
                        ->pluck("user_invite_id");


                    //even me
                    $member = User::whereIn('id',$achives)->get();
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
                        }else if ($day == 0){
                            $user->process = 100 ;
                        }else{
                            $user->process = null ;
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
                    $noti->detail = $general;
                    $noti->member = $member;
                    break;
                case 'publish':
                    $general = GeneralInfo::where("id",$general_id)->first()->toArray();
                    $publishInfo = PublishInfo::where('general_id',$general_id)
                        ->pluck("user_invite_id");
                    //even me
                    $member = User::whereIn('id',$publishInfo)->get();
                    $member= $member->map(function ($user){
                        $attachment = Attachment::where('id',$user->avatar_attachment_id)->first();
                        if ($attachment) {
                            [$thumb, $file] = $this->attachment_service->getThumbFile($attachment->file_type, $attachment->file);
                            $attachment->thumb = $thumb;
                            $attachment->file = $file;
                        }
                        $user->attachment = $attachment;
                        return $user;
                    });
                    $noti->detail = $general;
                    $noti->member = $member;
            }
            return $noti;
        });
        return $notifications[0];
    }

    public function myNotifications($args)
    {
        
        $notifications = Notification::where("user_receive_id", Auth::id())
            ->orderBy('id', 'desc')
            ->whereIn("type",$args["types"]);
        $notifications1 =  $notifications;
        $notifications = $notifications->get();

        $notifications1->update(['is_read' => 1]);
        $notifications = $notifications->map(function ($noti) {
            $user = User::where("id",$noti->user_id)->first();
            $user = $this->attachment_service->mappingAvatarBackgroud($user);
            $noti->user = $user;
            $messages = collect();
            switch ($noti->type) {
                case 'achieve':
                    $content = $noti->content;
                    if (@$content['status'] == 'pending') {
                        $messages->push('invite');
                    }
                    $generalInfo = $this->generalinfo_repository->find($content['general_id']);
                    if (@$generalInfo->task_id) {
                        $messages->push('task');
                        $task = $this->task_repository->find($generalInfo->task_id);
                        if(!$task) return;
                        $messages->push($task->name);
                        $noti->task_id = $generalInfo->task_id;
                    }
                    else if (@$generalInfo->goal_id) {
                        $messages->push('goal');
                        $goal = $this->goal_repository->find($generalInfo->goal_id);
                        if(!$goal) return;
                        $messages->push($goal->name);
                        $noti->goal_id = $generalInfo->goal_id;
                        //find goal
                    }else if (@$generalInfo->todolist_id) {
                        $messages->push('todolist');
                        $todo = $this->todolist_repository->find($generalInfo->todolist_id);
                        if(!$todo) return;
                        $messages->push($todo->name);
                        $noti->todolist_id = $generalInfo->todolist_id;
                        //find todolist
                    }else{
                        return;
                    }
                    break;
                case "publish":
                    $content = $noti->content;
                    $generalInfo = $this->generalinfo_repository->find($content['general_id']);
                    $PublishInfo =  $this->publish_info_repository->find($content['general_id'], Auth::id());
                    if (@$generalInfo->task_id) {
                        $messages->push('task');
                        $task = $this->task_repository->find($generalInfo->task_id);
                        if(!$task) return;
                        $messages->push($task->name);
                        $noti->task_id = $generalInfo->task_id;
                    }
                    else if (@$generalInfo->goal_id) {
                        $messages->push('goal');
                        $goal = $this->goal_repository->find($generalInfo->goal_id);
                        if(!$goal) return;
                        $messages->push($goal->name);
                        $noti->goal_id = $generalInfo->goal_id;
                        //find goal
                    }else if (@$generalInfo->todolist_id) {
                        $messages->push('todolist');
                        $todo = $this->todolist_repository->find($generalInfo->todolist_id);
                        if(!$todo) return;
                        $messages->push($todo->name);
                        $noti->todolist_id = $generalInfo->todolist_id;
                        //find todolist
                    }else{
                        return;
                    }
                    if(!@$PublishInfo->rule){
                        $messages->push("with rule: view");
                    }else  $messages->push("with rule: ".$PublishInfo->rule);
                break;
                case 'comment':
                    $content = $noti->content;
                    $generalInfo = $this->generalinfo_repository->find($content['general_id']);
                    if ($generalInfo) {
                        $user = User::where("id", @$content["user_id"])->first();
                        $messages->push("commented on a post that you're tagged in");
                        $noti->general_id = $content["general_id"];
                        if ($generalInfo->task_id){
                            $t = $this->task_repository->find($generalInfo->task_id);
                            if (!$t) return;
                            $noti->task_id = @$generalInfo->task_id;
                        }else {
                            return;
                        }
                    }else{
                        return;
                    }

                    break;
                case 'friend':
                    $content = $noti->content;
                    $messages->push('friend');
                    $user = User::where("id",$content["user_id"])->first();

                    $messages->push($user->name);
                    if ($content["status"] == "pending") {
                        $messages->push('sent you a friend request');
                    }
                    else {
                        $messages->push('accept');
                        $messages->push("accept you're friend request");
                    }
                    break;
                case 'edit_goal':
                    $content = $noti->content;
                    $key = array_key_first($content);
                    $user = User::where("id",$noti["user_id"])->first();
                    $goal = Goal::where("id",$noti->type_id)->first();

                    $messages
                        ->push($user->name ." change ".
                            $key." ".$content[$key]["old"]." to ".$content[$key]["new"].
                            " at your goal name ".$goal->name );
                    break;
            }
            $noti->messages = $messages;
            return $noti;
        });
        return $notifications->filter();
    }

    public function createNotification($args)
    {
        $args['user_id'] = Auth::id();
        return Notification::create($args);
    }

    public function updateNotification($args)
    {
        $args = array_diff_key($args, array_flip(['directive']));
        $update = tap(Notification::findOrFail($args["id"]))
            ->update($args);
        return $update;
    }

    public function deleteNotification($args)
    {
        $noti = Notification::find($args['id']);
        return $noti->delete();
    }

    public function saveNotification($type, $typeId, $content)
    {
        switch ($type) {
            case 'achieve':
                Notification::create([
                    'type'    => $type,
                    'type_id'  => $typeId,
                    'user_id' => $content['user_id'],
                    'user_receive_id' => $content['user_invite_id'],
                    'content' => $content,
                ]);
                $this->sendPushNotifi($content['user_invite_id']);
                break;
            case 'publish':
               $publish = Notification::create([
                    'type'    => $type,
                    'type_id'  => $typeId,
                    'user_id' => $content['user_id'],
                    'user_receive_id' => $content['user_invite_id'],
                    'content' => $content,
                ]);
                $this->sendPushNotifi($content['user_invite_id']);
                break;
            case 'comment':
                $comment = Comment::where("id",$typeId)->first();
                $general = GeneralInfo::where("id",$comment->general_id)->first();
                $user_recive_ids = $this->comment_repository
                    ->getUserCommentByGeneralId(@$comment->general_id)
                    ->merge($general->user_id)
                    ->toArray();

                $user_recive_ids = array_unique($user_recive_ids);
                foreach ($user_recive_ids as $user_id) {
                    if ($user_id == Auth::id()) continue;
                    $noti = Notification::create([
                        'type' => $type,
                        'type_id' => $typeId,
                        'user_id' => $content['user_id'],
                        'user_receive_id' => $user_id,
                        'content' => $content,
                    ]);
                }
                $this->sendPushNotifi($user_id);
                break;
            case 'friend':
                $friend = Friend::where("id",$typeId)->first();
                if ($friend->status == "pending"){
                    $user = @$friend->user_id;
                    $user_invite = @$friend->user_id_friend;
                }else{
                    $user = @$friend->user_id_friend;
                    $user_invite = @$friend->user_id;
                }
                    $noti = Notification::create([
                        'type' => $type,
                        'type_id' => $typeId,
                        'user_id' => $user,
                        'user_receive_id' => $user_invite,
                        'content' => $content,
                    ]);
                $this->sendPushNotifi($user_invite);
                break;
            case 'edit_goal':
                $goal = Goal::find($typeId);
                $noti = Notification::create([
                    'type' => $type,
                    'type_id' => $typeId,
                    'user_id' => Auth::id(),
                    'user_receive_id' => $goal->user_id,
                    'content' => $content,
                ]);
                $this->sendPushNotifi($goal->user_id);
                break;

        }
    }
    public function sendPushNotifi($user_recive){
        $noti = Notification::selectRaw("type, count(*) as count")
            ->where("user_receive_id",$user_recive)
            ->whereRaw("(is_read is null or is_read = 0)")
            ->groupBy("type")
            ->get()
            ->pluck('count', 'type')
            ->toArray();
        event(new \App\Events\NotificationMessage($noti,$user_recive));
    }

}
