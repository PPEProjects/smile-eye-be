<?php

namespace App\GraphQL\Mutations;
use App\Models\GeneralInfo;
use App\Models\Goal;
use App\Models\JapaneseGoal;
use App\Repositories\GeneralInfoRepository;
use App\Repositories\JapaneseGoalRepository;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;
use App\Repositories\NotificationRepository;

class JapaneseGoalMutations{
    private $japanese_goal_repository;
    private $generalinfo_repository;
    private $notification_repository;
    public function __construct(JapaneseGoalRepository $japanese_goal_repository, GeneralInfoRepository $generalinfo_repository, NotificationRepository $notificationRepository)
    {
        $this->japanese_goal_repository = $japanese_goal_repository;
        $this->generalinfo_repository = $generalinfo_repository;
        $this->notification_repository = $notificationRepository;
    }

    public function createJapaneseGoal($_,array $args){
        $args["user_id"] = Auth::id();
        if (!isset($args['type'])){
            throw new Error('You must input type');
        }

        if (isset($args['name_goal'])) {
            $dataGoal = ['name' => $args['name_goal'], 'user_id' => $args["user_id"]];
            if (isset($args['parent_id'])){
                $dataGoal['parent_id'] =  $args['parent_id'];
            }
            $goal = Goal::create($dataGoal);
            $this->generalinfo_repository
                ->setType('goal')
                ->upsert(array_merge($goal->toArray(), $args))
                ->findByTypeId($goal->id);
            $args['goal_id'] = $goal->id;
        }
       $jpGoal = $this->japanese_goal_repository->createJapaneseGoal($args);
        if ($args["type"] == "diary"){
            $more = $jpGoal->more;
            $more = array_shift($more);
            $user_invited_ids = $more["user_invited_ids"];
            $this->notification_repository->staticNotification("diary",$jpGoal->id,$jpGoal,$user_invited_ids);
        }
        if ($args["type"] == "flash_card"){
            $more = $jpGoal->more;
            if (isset($more["user_invited_ids"])){
                $user_invited_ids = $more["user_invited_ids"];
                $this->notification_repository->staticNotification("flash_card",$jpGoal->id,$jpGoal,$user_invited_ids);
            }
        }
        if ($args["type"] == "share_card_with_friend"){
            $more = $jpGoal->more;
            if (isset($more["user_invite_ids"])){
                $user_invited_ids = $more["user_invite_ids"];
                $this->notification_repository->staticNotification("share_card_with_friend",$jpGoal->id,$jpGoal,$user_invited_ids);
            }
        }
        if ($args["type"] == "make_video_share"){
            $more = $jpGoal->more;
            if (isset($more["user_invite_ids"])){
                $user_invited_ids = $more["user_invite_ids"];
                $this->notification_repository->staticNotification("make_video_share",$jpGoal->id,$jpGoal,$user_invited_ids);
            }
        }
        return $jpGoal;
    }
    public function updateJapaneseGoal($_,array $args){
        $args = array_diff_key($args, array_flip(['type']));
        return $this->japanese_goal_repository->updateJapaneseGoal($args);
    }
    public function deletejapaneseGoal($_,array $args){
        return $this->japanese_goal_repository->deletejapaneseGoal($args);
    }

}