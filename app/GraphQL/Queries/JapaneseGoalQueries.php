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
        $userId = Auth::id();
        $diary = User::find($userId)->japanese_goals;
        $diary = $diary->where("type",$args["type"])->sortByDESC('id');
        if ($args["type"] == "diary") {
          $result = $this->getDiary($diary);       
          return $result;
        }
        else return ;
    }
  public function myDiaryInvited($_,array $args){
    $userId = Auth::id();
    $diary = JapaneseGoal::where('type', 'diary')->get();
    $ids = [];
    foreach($diary as $value){
        if(array_intersect([$userId], @$value->more[0]['user_invite_ids'] ?? [])){
            $ids[] = $value->id;
        }
    }
    $diary = JapaneseGoal::whereIn('id', $ids)->orderBy('id', 'DESC')->get(); 
    $result = $this->getDiary($diary);
    return $result;
  }
  public function getDiary($diary)
  {
    $result = collect();
    $findIdGoals = $diary->pluck('goal_id');
    $getGoals = Goal::whereIn('id', $findIdGoals)->get()->keyBy('id');
    $nameGoals = $getGoals->toArray();
    
    foreach ($diary as $value) {     
       if(isset($nameGoals[$value->goal_id]['name'])){
            $user = User::select('id','name')->where('id', $value->user_id)->first();
            $result->push(["id" => $value->id, "user" => @$user->toArray() ,"goal_name"=>$nameGoals[$value->goal_id]['name'], "more"=>$value->more]);
        }else
        $result = $result->push(["id" => $value->id,"goal_name"=> Null,"more"=>$value->more]);         
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
 public function myFlashcardStudy($_,array $args){
    return $this->japanese_goal_repository->myFlashcardStudy($args);
}
}