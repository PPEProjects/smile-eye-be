<?php

namespace App\Repositories;


use App\Models\Attachment;
use App\Models\Goal;
use App\Models\JapaneseGoal;
use App\Models\JapaneseLearn;
use App\Repositories\JapaneseLearnRepository;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\AttachmentService;

class JapaneseGoalRepository
{
    private $japaneseLearn_repository;
    public function __construct(
        AttachmentService $attachment_service,
         NotificationRepository $notificationRepository,
         JapaneseLearnRepository $japaneseLearn_repository
         )
    {
        $this->attachment_service = $attachment_service;
        $this->notification_repository = $notificationRepository;
        $this->japaneseLearn_repository = $japaneseLearn_repository;
    }
    public function createJapaneseGoal($args){
        $japanese = JapaneseGoal::create($args);
        return $japanese;
    }
    public function updateJapaneseGoal($args){
        $diary = JapaneseGoal::find($args['id']);
        $userId = Auth::id();
        if($diary->type == "diary")
        {   
            $checkIdUser = array_intersect($diary->more[0]['user_invite_ids'], [$userId]);
            if($checkIdUser != [] && isset($args['more'][0]['other']))
            {
                $other = $args['more'][0]['other'];
                $args['more'] = $diary->more;
                $args['more'][0]['other_'.$userId] = $other;
                $args['more'][0]['review_'.$userId] = $other;
                $diary->more = $args['more'];
                $useInvite[] = $diary->user_id;
                $this->notification_repository->staticNotification("edit_diary",$diary->id,$diary, $useInvite);
            }
            else if($diary->user_id == $userId && isset($args['more'][0]['content']))
            {
                $content = $args['more'][0]['content'];
                $args['more'] = $diary->more;
                $args['more'][0]['content'] = $content;
            }else $args = array_diff_key($args, array_flip(['more']));
        }
        return tap(JapaneseGoal::findOrFail($args["id"]))
            ->update($args);
    }
    public function deletejapaneseGoal($args){
        $args = array_diff_key($args, array_flip(['directive']));
        $delete = JapaneseGoal::find($args['id']);
        return $delete->delete();
    }

    public function getJapaneseGoal($nameCollum, $value){

        $japaneseGoal = JapaneseGoal::where($nameCollum, $value)->get();
        return $japaneseGoal->sortByDESC('id');
    }
   
    public function detailJapaneseGoal($args){
        $nameCollum = "id";
        if (isset($args['goal_id'])){
           $nameCollum = "goal_id";
        }
        $value = $args[$nameCollum];
        $detailJPGoal = $this->getJapaneseGoal($nameCollum, $value)->first();
        if (isset($detailJPGoal->goal_id)) {
            $goalRoot = $this->findGoal($detailJPGoal->goal_id);
            while (true) {
                if (isset($goalRoot->parent_id)) {
                    $goalRoot = $this->findGoal($goalRoot->parent_id);
                } else break;
            }
            $detailJPGoal->goal_root = $goalRoot;
            $childrenIds = $this->japaneseLearn_repository->goalNochild([$goalRoot->id]);
            $findIds = array_search($detailJPGoal->goal_id,$childrenIds,true);
            $nextGoal =  @$this->findGoal($childrenIds[$findIds + 1]);
            $getType = @$this->getJapaneseGoal('goal_id', $childrenIds[$findIds + 1])->first();
            if(isset($nextGoal)){
                $nextGoal->type = @$getType->type;
            }       
            $detailJPGoal->next_goal = $nextGoal;
        }
        return $detailJPGoal;
    }

    public function findGoal($id){
        $goal = Goal::where('id',$id)->first();
        return $goal;
    }
    public function searchByTypeJapaneseGoal($args){

        return $this->getJapaneseGoal("type", $args['type']);
    }
}
