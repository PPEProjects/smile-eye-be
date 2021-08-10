<?php

namespace App\GraphQL\Queries;

use App\Models\Attachment;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\AttachmentService;

class UserQueries
{
    public function __construct(
        AttachmentService $attachmentService,
        NotificationRepository $notificationRepository,
        UserRepository $user_repository
    ) {
        $this->attachment_service = $attachmentService;
        $this->notificationRepository = $notificationRepository;
        $this->user_repository = $user_repository;
    }

    public function me()
    {
        $user = User::where("id", Auth::id())->first();
        $user->notification = Notification::selectRaw("type, count(*) as count")
            ->where("user_receive_id", Auth::id())
            ->whereRaw("(is_read is null or is_read = 0)")
            ->groupBy("type")
            ->get()
            ->pluck('count', 'type')
            ->toArray();
        $user = $this->attachment_service->mappingAvatarBackgroud($user);
        return $user;
    }
    public function listUser($_, array $args)
    {
        $users = User::selectRaw("id,name,email,phone_number,avatar_attachment_id")->get()->keyBy('id');
        $users = $users->map(function($user){ 
             $avatar = $this->attachment_service->mappingAvatarBackgroud($user);
             if($avatar->attachment != null)
             {
                $user->attachment = array_intersect_key(($user->attachment)->toArray(), array_flip(['id','thumb']));
             }
             else $user->attachment = null;
            return $user;
        });
        return $users;
    }
    public function user($_, array $args)
    {
        return $this->user_repository->user($args);
    }
}