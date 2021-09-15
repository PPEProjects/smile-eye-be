<?php

namespace App\Repositories;


use App\Models\Goal;
use App\Models\JapaneseGoal;
use App\Models\JapaneseLearn;
use App\Models\User;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\AttachmentService;

class JapaneseGoalRepository
{
    private $japaneseLearn_repository;

    public function __construct(
        GeneralInfoRepository $generalInfoRepository,
        AttachmentService $attachment_service,
        NotificationRepository $notificationRepository,
        JapaneseLearnRepository $japaneseLearn_repository
    ) {
        $this->generalinfo_repository = $generalInfoRepository;
        $this->attachment_service = $attachment_service;
        $this->notification_repository = $notificationRepository;
        $this->japaneseLearn_repository = $japaneseLearn_repository;
    }

    public function createJapaneseGoal($args)
    {
//        $args["user_id"] = Auth::id();
//        if (!isset($args['type'])) {
//            throw new Error('You must input type');
//        }
        if ($args['type'] == "diary" && isset($args['more'][0]['user_invite_ids'])) {
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
//        $jpGoal = $this->japanese_goal_repository->createJapaneseGoal($args);
        if ($args['type'] == 'flashcard_category') {
            $cate = $this->getJapaneseGoal('type', $args['type'])->first();
            if (isset($cate)) {
                $args['more'] = array_diff($args['more'], $cate->more);
                $args['more'] = array_merge($cate->more, $args['more']);
            }
            $jpGoal = JapaneseGoal::updateOrCreate(['type' => $args['type']], $args);
//            return $japaneseGoal;
        } else {
            $jpGoal = JapaneseGoal::create($args);
        }

        //        return $japanese;

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
        return $jpGoal;
        /*if ($args['type'] == 'flashcard_category') {
            $cate = $this->getJapaneseGoal('type', $args['type'])->first();
            if (isset($cate)) {
                $args['more'] = array_diff($args['more'], $cate->more);
                $args['more'] = array_merge($cate->more, $args['more']);
            }
            $japaneseGoal = JapaneseGoal::updateOrCreate(['type' => $args['type']], $args);
            return $japaneseGoal;
        }
        $japanese = JapaneseGoal::create($args);
        return $japanese;*/
    }

    public function updateJapaneseGoal($args)
    {
        if (isset($args['goal_id'])) {
            $japaneseGoal = JapaneseGoal::where('goal_id', $args['goal_id'])->first();
        } else {
            $japaneseGoal = JapaneseGoal::find($args['id']);
        }
        $userId = Auth::id();
        if ($japaneseGoal->type == "diary") {
            $checkIdUser = array_intersect($japaneseGoal->more[0]['user_invite_ids'], [$userId]);

            if ($checkIdUser != [] && isset($args['more'][0]['other'])) {
                $other = $args['more'][0]['other'];
                $args['more'] = $japaneseGoal->more;
                $args['more'][0]['other_' . $userId] = $other;
                $args['more'][0]['review_' . $userId] = $other;
                $japaneseGoal->more = $args['more'];
                $useInvite[] = $japaneseGoal->user_id;
                $this->notification_repository->staticNotification("edit_diary", $japaneseGoal->id, $japaneseGoal,
                    $useInvite);
            } else {
                if ($japaneseGoal->user_id == $userId && isset($args['more'][0]['content'])) {
                    $content = $args['more'][0]['content'];
                    $args['more'] = $japaneseGoal->more;
                    $args['more'][0]['content'] = $content;
                } else {
                    $args = array_diff_key($args, array_flip(['more']));
                }
            }
        }

        if ($japaneseGoal->type == 'sing_with_friend') {
            $user_invited_ids = array_diff($args['more']['user_invite_ids'],
                @$japaneseGoal->more['user_invite_ids'] ?? []);
            $userInvite = $args['more']['user_invite_ids'];
            $args['more'] = $japaneseGoal->more;
            $args['more']['user_invite_ids'] = $userInvite;
            $this->notification_repository->staticNotification("sing_with_friend", $japaneseGoal->goal_id,
                $japaneseGoal, $user_invited_ids);
        }
        return tap(JapaneseGoal::findOrFail($japaneseGoal->id))
            ->update($args);
    }

    public function deletejapaneseGoal($args)
    {
        $args = array_diff_key($args, array_flip(['directive']));
        $delete = JapaneseGoal::find($args['id']);
        return $delete->delete();
    }

    public function getJapaneseGoal($nameCollum, $value)
    {

        $japaneseGoal = JapaneseGoal::where($nameCollum, $value)->get();
        return $japaneseGoal->sortByDESC('id');
    }

    public function detailJapaneseGoal($args)
    {

        if (isset($args['goal_id'])) {
            $value = $args['goal_id'];
            $nameCollum = "goal_id";
        } else {
            if (isset($args['id'])) {
                $value = $args['id'];
                $nameCollum = "id";
            } else {
                return;
            }
        }
        $detailJPGoal = $this->getJapaneseGoal($nameCollum, $value)->first();
        if (isset($detailJPGoal->goal_id)) {
            $goalRoot = $this->findGoal($detailJPGoal->goal_id);
            while (true) {
                if (isset($goalRoot->parent_id)) {
                    $goalRoot = $this->findGoal($goalRoot->parent_id);
                } else {
                    break;
                }
            }
            $detailJPGoal->goal_root = $goalRoot;
            if ($detailJPGoal->type == 'communication' || $detailJPGoal->type == 'sing_with_friend') {
                $getListUsers = JapaneseLearn::where('goal_id', $detailJPGoal->goal_id)
                    ->whereNotIn('user_id', [Auth::id()])
                    ->OrDerBy('updated_at', 'DESC')->get();
                $listUsers = $getListUsers->pluck('user_id');
                $users = [];
                foreach ($listUsers as $id) {
                    $findUser = User::find($id);
                    $users[] = $this->attachment_service->mappingAvatarBackgroud($findUser)->toArray();
                }
                $detailJPGoal->list_users = @$users;
            }
            $childrenIds = $this->japaneseLearn_repository->goalNochild([$goalRoot->id]);
            $findIds = array_search($detailJPGoal->goal_id, $childrenIds, true);
            $keyNext = 0;
            $keyPrev = 0;
            foreach ($childrenIds as $key => $value) {
                if ($key > $findIds) {
                    if (isset($getTypeNextGoal)) {
                        continue;
                    }
                    $getTypeNextGoal = @$this->getJapaneseGoal('goal_id', $value)->first();
                    $keyNext = $value;
                }
                if ($key > 0 && $key <= $findIds) {
                    if (isset($getTypePrevGoal)) {
                        continue;
                    }
                    $getTypePrevGoal = @$this->getJapaneseGoal('goal_id', $childrenIds[$findIds - $key])->first();
                    $keyPrev = $childrenIds[$findIds - $key];
                }
            }
            $nextGoal = @$this->findGoal($keyNext);
            $prevGoal = @$this->findGoal($keyPrev);
            if (isset($nextGoal)) {
                $nextGoal->type = @$getTypeNextGoal->type;
            }
            if (isset($prevGoal)) {
                $prevGoal->type = @$getTypePrevGoal->type;
            }
            $detailJPGoal->next_goal = $nextGoal;
            $detailJPGoal->prev_goal = $prevGoal;
        }
        return $detailJPGoal;
    }

    public function findGoal($id)
    {
        $goal = Goal::where('id', $id)->first();
        return $goal;
    }

    public function searchByTypeJapaneseGoal($args)
    {

        return $this->getJapaneseGoal("type", $args['type']);
    }

    public function flashcardCategory($args)
    {
        $getCate = $this->getJapaneseGoal('type', 'flashcard');
        $cate = $this->getJapaneseGoal('type', 'flashcard_category')->first();
        if (!isset($getCate) || !isset($cate)) {
            return [];
        }
        $category = array_flip($cate->more);
        foreach ($category as $key => $value) {
            $category[$key] = [];
        }
        foreach ($getCate as $value) {
            if (isset($value->more["flashcard_category"])) {
                $value->more = array_merge(['id' => $value->id], $value->more);
                $category[$value->more["flashcard_category"]][] = $value->more;
            }
        }
        switch ($args['type']) {
            case "flashcard_category":
                //query sum card by category
                $flashCardCate = [];
                foreach ($category as $key => $value) {
                    $flashCardCate[] = [
                        'name'  => $key,
                        'count' => count($category[$key]),
                        'media' => @$category[$key][0]["front"]['media']
                    ];
                }
                break;
            default:
                $flashCardCate = ['name' => $args['type'], 'list' => @$category[$args['type']]];
        }
        return $flashCardCate;
    }

    public function myFlashcardStudy($args)
    {
        $japaneseGoal = $this->getJapaneseGoal('type', 'flashcard_study')
            ->where('user_id', Auth::id());
        if (isset($args['goal_id'])) {
            $japaneseGoal = $japaneseGoal->where('goal_id', $args['goal_id']);
        }
        $getIds = [];
        foreach ($japaneseGoal as $value) {
            $getIds = array_merge(current($value->more), $getIds);
        }
        $myFlashCard = JapaneseGoal::whereIn('id', $getIds)->get();
        return $myFlashCard;
    }
}
