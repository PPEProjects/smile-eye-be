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
        if ($args["type"] == "flash_card"){
            $cards = User::find(Auth::id())->japanese_goals;
            $cards = $cards->where("type","flash_card");
            foreach ($cards as $card){
                $result = $result->merge($card->more);
            }
            return ($result);
        }else if ($args["type"] == "basket_card") {
            $baskets = User::find(Auth::id())->japanese_goals;
            $baskets = $baskets->where("type","basket_card");
            foreach ($baskets as $basket){
                $result = $result->merge($basket->more);
            }
            return ($result);
        }
        return ;
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