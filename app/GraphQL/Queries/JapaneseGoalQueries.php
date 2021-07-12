<?php

namespace App\GraphQL\Queries;


use App\Models\JapaneseGoal;

class JapaneseGoalQueries
{
 public function detailJapaneseGoal($_,array $args){
     $temp = JapaneseGoal::find($args["id"]);
     return $temp = $temp ? $temp : null;
 }
}