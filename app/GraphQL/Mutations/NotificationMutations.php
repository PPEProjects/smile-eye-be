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

    public function updateNotification($_, array $args){
        return $this->notification_repository->updateNotification($args);
    }
    public function deleteNotification($_, array $args){
        return $this->notification_repository->deleteNotification($args);
    }

}
