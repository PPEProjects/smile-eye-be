<?php
namespace App\GraphQL\Mutations;




use App\Models\GeneralInfo;
use App\Models\PublishInfo;
use Illuminate\Support\Facades\Auth;
use App\Repositories\PublishInfoRepository;
use App\Repositories\NotificationRepository;

class PublishInfoMutations
{
    private $publish_info_repository ;
    private $notification_repository;
    public function __construct(PublishInfoRepository $publish_info_repository, NotificationRepository $notificationRepository)
    {
        $this->publish_info_repository = $publish_info_repository;
        $this->notification_repository = $notificationRepository;
    }

    public function createPublishInfo($_, array $args)
    {
        $publicInfo = $this->publish_info_repository->createPublishInfo($args);
        if(!$publicInfo){
            return false;
        }
        $publicInfo['user_id'] = Auth::id();
        $this->notification_repository->saveNotification('publish', $publicInfo['id'], $publicInfo);
        return true;
    }
    public function deletePublishInfo($_, array $args):bool
    {
        $publicInfo = PublishInfo::find($args['id']);

        return $publicInfo->delete();
    }
    public function updatePublishInfo($_, array $args)
    {
        return $this->publish_info_repository->updatePublishInfo($args);
    }
}
