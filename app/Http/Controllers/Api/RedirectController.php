<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\JapaneseGoal;
use App\Models\JapaneseLearn;

class RedirectController extends Controller
{
    //
    public function autoplay(Request $request ){
        $listGoals = Goal::where('root_id',$request->root_id)
                    ->orderByRaw('-`index` DESC')
                    ->get();
        $children = $listGoals->pluck('id')->toArray();
        $japaneseLearn = JapaneseLearn::where('user_id', $request->user_id)
                    ->whereIn('goal_id', @$children ?? [])
                    ->get();
        $getGoalIds = $japaneseLearn->pluck('goal_id')->toArray();
        $nextGoal = JapaneseGoal::where('goal_id', end($getGoalIds))
                    ->first();
        $type = @$nextGoal->type ?? '';
        $link = $this->requestLink($nextGoal, @$nextGoal->type);
        return redirect($link);
    }
    public function requestLink($block, $type = null)
    {
        $url = 'https://smileeye.edu.vn/m/';
        switch ($type) {
            case 'communication':
                $link = $url."CommunicationBlock_V2?goal_id=".$block->goal_id;
                break;
            case 'watch_video':
                $link = $url."ShowWatchVideo_V3?goal_id=".$block->goal_id;
                break;
            case 'vocabulary_grammar':
                $link = $url."WordGrammar_V2?goal_id=".$block->goal_id;
                break;
            case 'post':
                $link = $url."WatchPost_V6?goal_id=".$block->goal_id;
                break;
            case 'watch_diary':
                $link = $url."DiaryAndShare?goal_id=".$block->goal_id;
                break;
            case 'sing_with_friend':
                $link = $url."WatchSingingWithFriend_V3?goal_id=".$block->goal_id;
                break;
            case 'alphabet':
                $link = $url."LearnLetters_V2?goal_id=".$block->goal_id;
                break;
            case 'kanji':
                $link = $url."LearnKanjis_V7?goal_id=".$block->goal_id;
                break;break;
            default:
                $link = $url;
                break;
        }
        return $link;
    }
    public function nextBlock(Request $request){
        $goal = Goal::find($request->goal_id);
        $japaneseLearn = JapaneseLearn::updateOrCreate(
            ['user_id' => $request->user_id, 'goal_id' => $request->goal_id],
           ['user_id' => $request->user_id, 'goal_id' => $request->goal_id]
        );
        $goalRoot = Goal::find($goal->root_id);
        $listGoals = Goal::where('root_id', $goalRoot->id)->orderByRaw('-`index` DESC')->get();
        $trialIds = $this->findBlock($listGoals, @$goalRoot->trial_block ?? []);
        $checkTrial = in_array($request->goal_id, @$trialIds ?? []);
        $findIds = array_search($request->goal_id, @$trialIds ?? [] , true);   
        if($checkTrial)
        {  
            $numberTrial = count($trialIds);
            if(($numberTrial - 1) > $findIds){
                    $nextGoal = JapaneseGoal::where('goal_id',  $trialIds[$findIds + 1])->first();
            }
        }
        if(empty($nextGoal)) {
            $childrenIds = $this->findBlock($listGoals, [$goalRoot->id]);
            $findIds = array_search($request->goal_id, $childrenIds, true);
            $numberBlock = count($childrenIds);
            if(($numberBlock - 1) > $findIds)
            {
                $nextGoal = JapaneseGoal::where('goal_id',  $childrenIds[$findIds + 1])->first();
            }

        }
        $link = $this->requestLink(@$nextGoal, @$nextGoal->type);
        return redirect($link);
    }
    public function prevBlock(Request $request){
        
        $goal = Goal::find($request->goal_id);
        $goalRoot = Goal::find($goal->root_id);
        $listGoals = Goal::where('root_id', $goalRoot->id)->orderByRaw('-`index` DESC')->get();
        $trialIds = $this->findBlock($listGoals, @$goalRoot->trial_block ?? []);
        $checkTrial = in_array($request->goal_id, @$trialIds ?? []);
        $findIds = array_search($request->goal_id, @$trialIds ?? [] , true);   
        if($checkTrial)
        {  
            $numberTrial = count($trialIds);
            if($findIds > 0 && $findIds < ($numberTrial - 1)){
                    $prevGoal = JapaneseGoal::where('goal_id',  $trialIds[$findIds - 1])->first();
            }
        }
        if(empty($prevGoal)) {
            $childrenIds = $this->findBlock($listGoals, [$goalRoot->id]);
            $findIds = array_search($request->goal_id, $childrenIds, true);
            $numberBlock = count($childrenIds);
            if($findIds > 0 && $findIds < ($numberBlock - 1))
            {
                $prevGoal = JapaneseGoal::where('goal_id',  $childrenIds[$findIds - 1])->first();
            }
        }
        $link = $this->requestLink(@$prevGoal, @$prevGoal->type);
        return redirect($link);
    }
    // public function findBlock($listGoals, $ids, $children = [])
    // {
    //     $getchildren = $children;
    //     $goals = $listGoals;
    //     foreach($ids as $value)
    //     {
    //         $find = $goals->where('parent_id', $value);
    //         if($find->toArray() != []){
    //             $idParent = $find->pluck('id')->toArray();
    //             $getchildren =  self::findBlock($listGoals, $idParent, $getchildren);
    //         }
    //         else{
    //             $checkBlock = $goals->where('id', $value)->first();
    //             if(isset($checkBlock->japaneseGoal)){
    //                 $getchildren[] = (string)$value;
    //             }
    //         }
    //     }
    //     return $getchildren;
    // }
    public function findBlock($listGoals, $ids = [], $children = [])
    {
        $findGoal = [];
        $listTrial = $listGoals->whereIn('parent_id', $ids);
        $idParent = @$listTrial->pluck('id')->toArray() ?? [];
        while(true){
            $findGoal = array_merge($findGoal, $idParent);
            $listTrial = $listGoals->whereIn('parent_id', $idParent);
            $idParent = @$listTrial->pluck('id')->toArray() ?? [];
            if ($idParent == []) {
                break;
            }
        }
        $jpGoal = JapaneseGoal::whereIn('goal_id', @$findGoal ?? [])->get();
        $getids = $jpGoal->pluck('goal_id');
        $goals = Goal::whereIn('id', @$getids ?? [])
                        ->orderByRaw('-`index` DESC')
                        ->get()
                        ->pluck('id')
                        ->toArray();
        return $goals;
    }
    public function listBlock($listGoal)
    {
        $idListGoals = $listGoal->pluck('id');
        $jpGoal = JapaneseGoal::whereIn('goal_id', @$idListGoals ?? [])->get();
        $getids = $jpGoal->pluck('goal_id');
        $goals = Goal::whereIn('id', @$getids ?? [])
                        ->orderByRaw('-`index` DESC')
                        ->get()
                        ->pluck('id')
                        ->toArray();
        return $goals;
    }
}
