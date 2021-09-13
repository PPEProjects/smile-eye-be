<?php
namespace App\GraphQL\Mutations;

use App\Models\Notification;
use App\Repositories\NotificationRepository;
use Illuminate\Support\Facades\Auth;

class NotificationMutations
{
    private $notification_repository;
    public function __construct(NotificationRepository $notification_repository)
    {
        $this->notification_repository = $notification_repository;
    }

    public function createNotification($_, array $args){
        return $this->notification_repository->createNotification($args);
    }
    public function createInviteAny($_, array $args){
        return $this->notification_repository->createNotification($args, "invite_any");
    }
    public function createInviteFriend($_, array $args){
        return $this->notification_repository->createNotification($args, "invite_friend");
    }
    public function createInviteAnyPass($_, array $args){
        return $this->notification_repository->createNotification($args, "invite_any_pass");
    }
    public function updateNotification($_, array $args){
        return $this->notification_repository->updateNotification($args);
    }
    public function deleteNotification($_, array $args){
        if(isset($args['type'])){
           $notification = Notification::where('type',$args['type'])
                    ->where('type_id', $args['type_id'])->get();
            if($notification->toArray() == [])
            {
                return false;
            }
           foreach($notification as $value){
              $args['id'] = $value->id;
              $noti =  $this->notification_repository->deleteNotification($args);
           }
           return $noti;
        }
        return $this->notification_repository->deleteNotification($args);
    }

}
