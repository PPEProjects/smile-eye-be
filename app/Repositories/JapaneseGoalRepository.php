<?php

namespace App\Repositories;


use App\Models\Attachment;
use App\Models\Goal;
use App\Models\JapaneseGoal;
use App\Models\JapaneseLearn;
use App\Models\User;
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
        if($args['type'] == 'flashcard_category'){
            $cate = $this->getJapaneseGoal('type', $args['type'])->first();
            if(isset($cate)) {
                $args['more'] = array_diff($args['more'], $cate->more);
                $args['more'] = array_merge($cate->more, $args['more']);
            }
            $japaneseGoal =  JapaneseGoal::updateOrCreate(['type' => $args['type']],$args);
            return $japaneseGoal;
        }
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
            if($detailJPGoal->type == 'communication' || $detailJPGoal->type == 'sing_with_friend' ) {
                $getListUsers = JapaneseLearn::where('goal_id', $detailJPGoal->goal_id)
                                                ->whereNotIn('user_id', [Auth::id()])
                                                ->OrDerBy('updated_at', 'DESC')->get();
                $listUsers = $getListUsers->pluck('user_id');
                $users =[];
                foreach ($listUsers as $id) {
                    $findUser = User::find($id);
                    $users[] = $this->attachment_service->mappingAvatarBackgroud($findUser)->toArray();
                }
                $detailJPGoal->list_users = @$users;
            }
            $childrenIds = $this->japaneseLearn_repository->goalNochild([$goalRoot->id]);
            $findIds = array_search($detailJPGoal->goal_id,$childrenIds,true);
            $keyNext = 0;
            $keyPrev = 0;
            foreach ($childrenIds as $key => $value) {
                if ($key > $findIds) {
                    if (isset($getTypeNextGoal)){
                        continue;
                    }
                    $getTypeNextGoal = @$this->getJapaneseGoal('goal_id', $value)->first();
                    $keyNext = $value;
                }
                if ($key > 0  && $key <= $findIds) {
                    if (isset($getTypePrevGoal)){
                        continue;
                    }
                    $getTypePrevGoal = @$this->getJapaneseGoal('goal_id', $childrenIds[$findIds - $key])->first();
                    $keyPrev = $childrenIds[$findIds - $key];
                }
            }
            $nextGoal =  @$this->findGoal($keyNext);
            $prevGoal =  @$this->findGoal($keyPrev);
            if(isset($nextGoal)){
                $nextGoal->type = @$getTypeNextGoal->type;      
            }       
            if(isset($prevGoal)){
                $prevGoal->type = @$getTypePrevGoal->type;
            }
            $detailJPGoal->next_goal = $nextGoal;
            $detailJPGoal->prev_goal = $prevGoal;
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

   public function flashcardCategory($args){
        $getCate = $this->getJapaneseGoal('type', 'flashcard');
        $cate = $this->getJapaneseGoal('type', 'flashcard_category')->first();
        if(!isset($getCate) || !isset($cate)){
            return  [];
        }
        $category = array_flip($cate->more);
        foreach($category as $key => $value){
            $category[$key] = [];
        }
       foreach($getCate as $value){
            $category[$value->more["flashcard_category"]][] = $value->more;
        }

       switch ($args['type']) {
           case "flashcard_category":
               //query sum card by category
               $flashCardCate = [];
               foreach($category as $key => $value){
                   $flashCardCate[] =[ 'name' => $key,'count' => count($category[$key]),
                                        'image' => current(@$category[$key])["front"]['image'] ?? [] ];
               }
               break;
           default:
               $flashCardCate = [ 'name' => $args['type'], 'list' => @$category[$args['type']] ?? [] ];
       }
       return $flashCardCate;
   }

}
