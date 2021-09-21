<?php

namespace App\GraphQL\Mutations;

use App\Models\JapaneseGoal;
use App\Repositories\GeneralInfoRepository;
use App\Repositories\JapaneseGoalRepository;
use App\Repositories\NotificationRepository;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

class JapaneseGoalMutations
{
    private $japanese_goal_repository;
    private $generalinfo_repository;
    private $notification_repository;

    public function __construct(
        JapaneseGoalRepository $japanese_goal_repository,
        GeneralInfoRepository $generalinfo_repository,
        NotificationRepository $notificationRepository
    ) {
        $this->japanese_goal_repository = $japanese_goal_repository;
        $this->generalinfo_repository = $generalinfo_repository;
        $this->notification_repository = $notificationRepository;
    }

    public function createJapaneseGoal($_, array $args)
    {
        $args["user_id"] = Auth::id();
        if (!isset($args['type'])) {
            throw new Error('You must input type');
        }
        return $this->japanese_goal_repository->createJapaneseGoal($args);
        /*if ($args['type'] == "diary" && isset($args['more'][0]['user_invite_ids'])) {
            $idUserInvited = $args['more'][0]['user_invite_ids'];
            foreach ($idUserInvited as $value) {
                $args['more'][0]['other_' . $value] = $args['more'][0]['content'];
            }
        }
        if (isset($args['name_goal'])) {
            $dataGoal = ['name' => $args['name_goal'], 'user_id' => $args["user_id"]];
            if (isset($args['parent_id'])) {
                $dataGoal['parent_id'] = $args['parent_id'];
            }
            $goal = Goal::create($dataGoal);
            $this->generalinfo_repository
                ->setType('goal')
                ->upsert(array_merge($goal->toArray(), $args))
                ->findByTypeId($goal->id);
            $args['goal_id'] = $goal->id;
        }
        $jpGoal = $this->japanese_goal_repository->createJapaneseGoal($args);
        if ($args["type"] == "diary") {
            $more = $jpGoal->more;
            $more = array_shift($more);
            if (isset($more["user_invite_ids"])) {
                $user_invited_ids = $more["user_invite_ids"];
                $this->notification_repository->staticNotification("diary", $jpGoal->id, $jpGoal, $user_invited_ids);
            } else {
                if (isset($more["user_invited_ids"])) {
                    throw new Error("'user_invited_ids' => 'user_invite_ids'");
                }
            }
        }
        if ($args["type"] == "flash_card") {
            $more = $jpGoal->more;
            if (isset($more["user_invite_ids"])) {
                $user_invited_ids = $more["user_invite_ids"];
                $this->notification_repository->staticNotification("flash_card", $jpGoal->id, $jpGoal,
                    $user_invited_ids);
            } else {
                if (isset($more["user_invited_ids"])) {
                    throw new Error("'user_invited_ids' => 'user_invite_ids'");
                }
            }
        }
        if ($args["type"] == "sing_with_friend") {
            $more = $jpGoal->more;
            if (isset($more["user_invite_ids"])) {
                $user_invited_ids = $more["user_invite_ids"];
                $this->notification_repository->staticNotification("sing_with_friend", $jpGoal->goal_id, $jpGoal,
                    $user_invited_ids);
            }
        }
        if ($args["type"] == "share_card_with_friend") {
            $more = $jpGoal->more;
            if (isset($more["user_invite_ids"])) {
                $user_invited_ids = $more["user_invite_ids"];
                $this->notification_repository->staticNotification("share_card_with_friend", $jpGoal->id, $jpGoal,
                    $user_invited_ids);
            }
        }
        if ($args["type"] == "make_video_share") {
            $more = $jpGoal->more;
            if (isset($more["user_invite_ids"])) {
                $user_invited_ids = $more["user_invite_ids"];
                $this->notification_repository->staticNotification("make_video_share", $jpGoal->id, $jpGoal,
                    $user_invited_ids);
            }
        }
        return $jpGoal;*/
    }

    public function updateJapaneseGoal($_, array $args)
    {
        $args = array_diff_key($args, array_flip(['type']));
        return $this->japanese_goal_repository->updateJapaneseGoal($args);
    }

    public function upsertJapaneseGoal($_, array $args)
    {
        if (!empty($args['id'])) {
            $args = array_diff_key($args, array_flip(['type']));
            \Illuminate\Support\Facades\Log::channel('single')->info('$args', [$args]);
            
            return $this->japanese_goal_repository->updateJapaneseGoal($args);
        }
        $args["user_id"] = Auth::id();
        if (!isset($args['type'])) {
            throw new Error('You must input type');
        }
        return $this->japanese_goal_repository->createJapaneseGoal($args);
//        $args = array_diff_key($args, array_flip(['type']));
//        return $this->japanese_goal_repository->updateJapaneseGoal($args);
//        $passwordReset = PasswordReset::updateOrCreate([
//            'email' => $user->email,
//        ], [
//            'token' => rand(000000,999999),
//        ]);
    }

    public function deletejapaneseGoal($_, array $args)
    {
        return $this->japanese_goal_repository->deletejapaneseGoal($args);
    }

    public function updateMeetUrl_sing_with_friend($_, array $args)
    {
        $jpGoal = JapaneseGoal::where('goal_id', $args['goal_id'])
            ->where('type', 'sing_with_friend')
            ->first();
        if (!$jpGoal) {
            throw new Error('Japanese Goal not found');
        }
        $jpGoal = $jpGoal->toArray();
        $jpGoal['more']['meet'] = $args['meet'];
        return (bool)JapaneseGoal::where('id', $jpGoal['id'])
            ->update(['more' => $jpGoal['more']]);
    }
    public function renameFlashcardCategory($_, array $args)
    {
        return $this->japanese_goal_repository->renameFlashcardCategory($args);
    }
}