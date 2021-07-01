<?php

namespace App\GraphQL\Queries;

use App\Repositories\NotificationRepository;

class NotificationQueries
{
    private $notification_repository;

    public function __construct(
        NotificationRepository $notification_repository
    ) {
        $this->notification_repository = $notification_repository;
    }

    public function myNotifications($_,array $args)
    {
        return $this->notification_repository->myNotifications($args);
    }

    public function detailNotifications($_, array $args)
    {
     return $this->notification_repository->detailNotifications($args);
    }

    public function NotificationsByType($_, array $args)
    {
        $noti = $this->notification_repository->myNotifications();
        $type = $args['type'];
        return $noti->where("type", "=", $type);
    }
}