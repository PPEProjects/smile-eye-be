<?php
namespace App\GraphQL\Mutations;

use App\Models\Achieve;
use App\Models\Comment;
use App\Models\ContestInfo;
use App\Models\GeneralInfo;
use App\Models\Notification;
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
        if(isset($args['goal_id'])){
            $general = GeneralInfo::where('goal_id',  $args['goal_id'])->first();
            $achive = Achieve::where("general_id",  @$general->id)
                                ->where('user_invite_id', Auth::id())
                                ->first();
            if(isset($achive)){
                $notification = Notification::where('type_id', $achive->id)->first();
                $notification->delete;
                return $achive->delete();
            }
            return false;
        }
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
