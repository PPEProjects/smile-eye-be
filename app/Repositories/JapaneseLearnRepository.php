<?php

namespace App\Repositories;
use App\Models\JapaneseLearn;
use App\Models\User;
use App\Models\Goal;
use App\Models\JapaneseGoal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class JapaneseLearnRepository
{
    // Mutations
    public function upsertJapaneseLearn($args)
    {
        $args['user_id'] = Auth::id();
        $args['updated_at'] = date('Y-m-d H:m:s');
        $japaneseLearn = JapaneseLearn::updateOrCreate(
            ['user_id' => $args['user_id'], 'goal_id' => $args['goal_id']],
            $args
        );
        return $japaneseLearn;
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
          $find = Goal::where('parent_id', $value)->orderBy('id', 'desc')->get(); 
            if($find->toArray() != []){
                $idParent = $find->pluck('id')->toArray();
                $getchildren =  self::goalNochild($idParent, $getchildren);
            }
            else  $getchildren[] = $value;  
       }
       return $getchildren;
    }
}
