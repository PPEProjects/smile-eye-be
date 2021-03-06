<?php

namespace App\Repositories;

use App\Models\Friend;
use App\Models\Goal;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use ppeCore\dvtinh\Services\AttachmentService;
use App\Repositories\FriendRepository;
use App\Repositories\GeneralInfoRepository;

class UserRepository
{
    public function __construct(AttachmentService $attachment_service, GeneralInfoRepository $generalinfo_repository)
    {
        $this->attachment_service = $attachment_service;
        $this->generalinfo_repository = $generalinfo_repository;
    }

    public function getByIds($userIds)
    {
        $users = User::whereIn('id', $userIds)
            ->get()
            ->map(function ($user){
                $user = $this->attachment_service->mappingAvatarBackgroud($user);
                return $user;
            });
        return $users;
    }
    public function user($args)
    {
        $user = User::where('id', $args["id"])
            ->first();
        $goals = User::find($args["id"])->goals;
        $goals = $goals->whereNull("parent_id");
        foreach ($goals as $g){
            $generalInfo = $this->generalinfo_repository
                ->setType('goal')
                ->findByTypeId($g->id);
            $g->general_info = $generalInfo;
        }
        $user->goals = $goals;
//        $user->total_goal = count($goals->whereNull("parent_id"));
        $user->total_goal = count($goals);
        //my friend
        $friend = Friend::whereRaw("(user_id = {$args["id"]} 
                            OR user_id_friend = {$args["id"]})
                            AND status like 'accept'");
//            ->get();
//        dd($friend->toSql());
        $friend_user_id = $friend->pluck("user_id");
        $friend_user_id_friend = $friend->pluck("user_id_friend");

        $friend_ids = array_flip($friend_user_id->merge($friend_user_id_friend)->toArray());
//        dd($friend_ids);
        $temp = [$args["id"]];
        $friend_ids = array_diff_key($friend_ids,array_flip($temp));

        $friends = $this->getByIds(array_flip($friend_ids));
        $user->my_friends = $friends;
        $user->total_friend = count($friends);
        //add attachment
        $attachments = User::find($args["id"])->attachments;
        $attachments = $attachments->map(function ($att){
            [$thumb, $file] = $this->attachment_service->getThumbFile($att->file_type,$att->file);
            $att->thumb = $thumb;
            $att->file = $file;
            return $att ;
        });
        $user->attachments = $attachments;
        $user = $this->attachment_service->mappingAvatarBackgroud($user);
        // add status for viewer

        $auth = Auth::id();
        $status = null ;
        $raw = Friend::where("user_id",Auth::id())
            ->where("user_id_friend",$args["id"])
            ->first();
        if ($raw) {
            $status = $raw->status;
            }else{
            $raw = Friend::where("user_id",$args["id"])
                ->where("user_id_friend",Auth::id())
                ->first();
            if ($raw){
                if ($raw->status == "accept"){
                    $status = "accept";
                }else{
                    $status = "waiting";
                }

            }
        }
        $user->friend_status = $status;
        return $user;
    }

    public function getWithoutIds($userIds)
    {
            $users = User::WhereNotIn('id' ,$userIds)->get();
            $ids = $users->pluck('id');
            return $users;
    }
    public function updateUser($args)
    {
        $args = array_diff_key($args, array_flip(['directive', 'email']));
        $update = tap(User::findOrFail(Auth::id()))
            ->update($args);
        return $update;
    }

}
