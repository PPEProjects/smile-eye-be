<?php
namespace App\GraphQL\Mutations;

use App\Models\Achieve;
use App\Models\Comment;
use App\Models\ContestInfo;
use App\Repositories\AchieveRepository;
use App\Repositories\NotificationRepository;
use Illuminate\Support\Facades\Auth;
use App\Repositories\AssignInfoRepository;

class AchieveMutations
{
    private $achieve_repository ;
    public function __construct(
        AchieveRepository $Achieverepository,
        NotificationRepository $notificationRepository)
    {
        $this->notification_repository = $notificationRepository;
        $this->achieve_repository = $Achieverepository;

    }

    public function createAchieve($_, array $args): bool
    {
        $args['user_id'] = Auth::id();
        $achieves = $this->achieve_repository->createAchieve($args);
        foreach ($achieves as $achieve) {
            $this->notification_repository->saveNotification('achieve', $achieve['id'], $achieve);
        }
        if ($achieves == []){
            return false;
        }
        return true;
    }
    public function updateAchieve($_, array $args)
    {
        return $this->achieve_repository->updateAchieve($args);
    }
    public function deleteAchieve($_, array $args):bool
    {
        $cm = Achieve::find($args['id']);
        return $cm->delete();
    }
    public function updateAchieveWithGeneralId($_, array $args){
        return $this->achieve_repository->updateAchieveWithGeneralId($args);
    }
    public function addTemplate($_, array $args){
        return $this->achieve_repository->addTemplate($args);
    }

}
