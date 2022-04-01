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
        $today = date('Y-m-d');
        $checkJapaneseLearn = JapaneseLearn::where('user_id', $args['user_id'])
                                            ->where('goal_id', $args['goal_id'])
                                            ->whereRaw("DATE_FORMAT(updated_at, '%Y-%m-%d') = '".$today."'")
                                            ->first();
        if(isset($checkJapaneseLearn)) {
            $japaneseLearn = JapaneseLearn::updateOrCreate(
                ['user_id' => $args['user_id'], 'goal_id' => $args['goal_id']],
                $args
            );
        }
        else {
            $args['updated_at'] = date('Y-m-d H:i:s');
            $japaneseLearn = JapaneseLearn::create($args);
        }
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
          $find = Goal::where('parent_id', $value)->orderByRaw('-`index` DESC')->get(); 
            if($find->toArray() != []){
                $idParent = $find->pluck('id')->toArray();
                $getchildren =  self::goalNochild($idParent, $getchildren);
            }
            else  $getchildren[] = (string)$value;  
       }
       return $getchildren;
    }

    public  function progressUserJapaneseLearn($args){
        $days = ['Mon',  'Tue' , 'Wed',
                'Thu' , 'Fri' , 'Sat' , 'Sun'];
        $japaneseLearn = JapaneseLearn::selectRaw("*, DATE_FORMAT(updated_at, '%a') as 'day',
                                            DATE(CONVERT_TZ(created_at , '+00:00', '+07:00')) as 'date'")
                                        ->whereRaw("DATE(CONVERT_TZ(updated_at , '+00:00', '+07:00')) 
                                                    between date_sub(now(),INTERVAL 1 WEEK) and now() ")
                                        ->OrderBy('updated_at', 'DESC')
                                        ->get();
        $listUser = [];
        $progress = [];
        $now = date("l/Y-m-d");
        $getDate = explode("/",$now);
        $date = $getDate[0];
//        if(isset($args['goal_root'])) {
//            $name = $args['goal_root'];
//            $japaneseLearn = $japaneseLearn->filter(function ($learn) use ($name) {
//                return false !== stristr(@$learn->goal->goal_root->name, $name);
//            });
//        }
         foreach ($japaneseLearn as $jp) {
             if (!isset($jp->goal->goal_root)
                 || empty($jp->user->name)) {
                 continue;
             }
             if (!isset($listUser[$jp->user_id . "" . $jp->goal->root_id])) {
                 $listUser[$jp->user_id . "" . $jp->goal->root_id]['japanese_learn_id'] = $jp->id;
                 $listUser[$jp->user_id . "" . $jp->goal->root_id]['user_id'] = $jp->user_id;
                 $listUser[$jp->user_id . "" . $jp->goal->root_id]['goal_id'] = $jp->goal_id;
                 $listUser[$jp->user_id . "" . $jp->goal->root_id]['goal_root_id'] = $jp->goal->root_id;
                 $listUser[$jp->user_id . "" . $jp->goal->root_id]['name'] = $jp->user->name;
                 $listUser[$jp->user_id . "" . $jp->goal->root_id]['name_goal_root'] = @$jp->goal->goal_root->name;
                 $listUser[$jp->user_id . "" . $jp->goal->root_id]['current_topic'] = @$jp->goal->name;
                 $listUser[$jp->user_id . "" . $jp->goal->root_id]['start_date'] = $jp->date;
             }
             $progress[$jp->user_id][$jp->goal->root_id][$jp->day][] = $jp->id;
             $count = 1;
             $thisWeek = 0;
             foreach ($days as $value) {
                 $sumLearnInDate = 0;
                 $week = date('Y-m-d', strtotime(' - ' . $thisWeek . ' day ' . $date));
                 $getDay = date('D', strtotime($week));
                 if (isset($progress[$jp->user_id][$jp->goal->root_id][$getDay])) {
                     $count++;
                     $sumLearnInDate = count($progress[$jp->user_id][$jp->goal->root_id][$getDay]);
                 }
                 $listUser[$jp->user_id . "" . $jp->goal->root_id][$getDay] = $sumLearnInDate;
                 $listUser[$jp->user_id . "" . $jp->goal->root_id]['date' . $getDay] = $week;
                 $thisWeek++;
             }

             $listUser[$jp->user_id . "" . $jp->goal->root_id]['support'] = ($count <= 5) ? true : false;
         }

         return array_values($listUser);
    }
}
