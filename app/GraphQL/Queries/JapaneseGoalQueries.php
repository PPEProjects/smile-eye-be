<?php

namespace App\GraphQL\Queries;

use App\Models\Goal;
use App\Models\JapaneseGoal;
use App\Models\User;
use App\Repositories\JapaneseGoalRepository;
use Illuminate\Support\Facades\Auth;

class JapaneseGoalQueries
{
    private $japanese_goal_repository;

    public function __construct(JapaneseGoalRepository $japanese_goal_repository)
    {
        $this->japanese_goal_repository = $japanese_goal_repository;
    }

    public function detailJapaneseGoal($_,array $args){
     return $this->japanese_goal_repository->detailJapaneseGoal($args);
 }
 public function searchByTypeJapaneseGoal($_,array $args){
     return $this->japanese_goal_repository->searchByTypeJapaneseGoal($args);
 }

 public function basketCards(){
        return JapaneseGoal::where("type","basket_card")
            ->orderBy("id","desc")
            ->get();
 }
 public function detailBasketCard($_,array $args){
        return JapaneseGoal::findOrFail($args["id"]);
 }

 public function myBasketOrCard($_,array $args){
        $result = collect();
        $cards = User::find(Auth::id())->japanese_goals;
        $cards = $cards->where("type",$args["type"]);
        foreach ($cards as $card) {
            $result = $result->merge($card->more);
        }
        return $result;
 }
    public function myDiary($_,array $args){
        $result = collect();
        $cards = User::find(Auth::id())->japanese_goals;
        $findIdGoals = $cards->pluck('goal_id');
        $getGoals = Goal::whereIn('id', $findIdGoals)->get()->keyBy('id');
        $nameGoals = $getGoals->toArray();
        $cards = $cards->where("type",$args["type"]);
        if ($args["type"] == "diary") {
            foreach ($cards as $card) {        
                if(isset($nameGoals[$card->goal_id]['name'])){
                    $result->push(["goal_name"=>$nameGoals[$card->goal_id]['name'], "more"=>$card->more]);
                }else
                $result = $result->push(["goal_name"=> Null,"more"=>$card->more]);         
            }
        }
        return $result;
    }

 public function flashCards(){
     return JapaneseGoal::where("type","flash_card")
         ->orderBy("id","desc")
         ->get();
 }
 public function detailFlashCard($_,array $args){
     return JapaneseGoal::findOrFail($args["id"]);
 }

 public function flashcardCategory($_,array $args){
        return $this->japanese_goal_repository->flashcardCategory($args);
 }

}