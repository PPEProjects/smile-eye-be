<?php
namespace App\GraphQL\Mutations;




use App\Models\GeneralInfo;
use App\Models\PublishInfo;
use Illuminate\Support\Facades\Auth;
use App\Repositories\PublishInfoRepository;

class PublishInfoMutations
{
    private $publish_info_repository ;
    public function __construct(PublishInfoRepository $publish_info_repository)
    {
        $this->publish_info_repository = $publish_info_repository;
    }

    public function createPublishInfo($_, array $args)
    {
        $args['user_id'] = Auth::id();
        return PublishInfo::create($args);
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
