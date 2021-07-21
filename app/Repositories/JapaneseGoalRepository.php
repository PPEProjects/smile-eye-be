<?php

namespace App\Repositories;


use App\Models\Attachment;
use App\Models\Goal;
use App\Models\JapaneseGoal;
use ppeCore\dvtinh\Services\AttachmentService;

class JapaneseGoalRepository
{
    public function __construct(AttachmentService $attachment_service)
    {
        $this->attachment_service = $attachment_service;
    }
    public function createJapaneseGoal($args){
        $japanese = JapaneseGoal::create($args);
        return $japanese;
    }
    public function updateJapaneseGoal($args){
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
