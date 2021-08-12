<?php

namespace App\Repositories;
use App\Models\JapaneseLearn;
use App\Models\User;
use App\Models\Goal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JapaneseLearnRepository
{
    // Mutations
    public function createJapaneseLearn($args)
    {
        $args['user_id'] = Auth::id();
        return JapaneseLearn::create($args);
    }   
    public function updateJapaneseLearn($args)
    {
        $args['user_id'] = Auth::id();
        return tap(JapaneseLearn::findOrFail($args["id"]))
               ->update($args);
    }   
    public function deleteJapaneseLearn($args)
    {
        $delete = JapaneseLearn::find($args['id']);
        return $delete->delete();
    }

    //Queries
    public function myJapaneseLearn(){
        return JapaneseLearn::where("user_id",Auth::id())
            ->orderBy("id","desc")
            ->get();
    }
    public function detailJapaneseLearn($args){
        $japaneseLearn = JapaneseLearn::findOrFail($args["id"]);
        $goals = Goal::where('parent_id', $japaneseLearn->goal_id)->get();
        $getIds = $goals->pluck('id')->toArray();
        $children  = self::goalNochild($getIds);
        $goalNoChilds = Goal::whereIn('id', $children)->OrderBy('parent_id', 'desc')->get();
        $japaneseLearn->goal_no_childs =  $goalNoChilds;
        return $japaneseLearn;
    }
    public function goalNochild($ids, $children = [])
    {
        $getchildren = $children;
        foreach($ids as $value)
        {
          $find = Goal::where('parent_id', $value)->get();
          if($find->toArray() != []){
              $idParent = $find->pluck('id')->toArray();      
          }
           else{
            $getchildren[] = $value;
           }   
       }
       if(isset($idParent)){
        $getchildren = self::goalNochild($idParent, $getchildren);
       }
       return $getchildren;
    }
}
