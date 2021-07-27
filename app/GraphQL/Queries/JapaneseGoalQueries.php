<?php

namespace App\GraphQL\Queries;


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
        $cards = $cards->where("type",$args["type"]);
        if ($args["type"] == "diary") {
            foreach ($cards as $card) {
                $result = $result->push($card->more);
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
}