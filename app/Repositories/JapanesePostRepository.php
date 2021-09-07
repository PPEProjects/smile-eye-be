<?php

namespace App\Repositories;
use App\Models\JapaneseLearn;
use App\Models\User;
use App\Models\Goal;
use App\Models\JapaneseGoal;
use App\Models\JapanesePost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class JapanesePostRepository{

    public function createJapanesePost($args)
    {
        return JapanesePost::create($args);
    }

    public function updateJapanesePost($args)
    {    
//        if(isset($args['goal_id'])){
//            $getId = JapanesePost::where('goal_id', $args['goal_id'])->first();
//            $args['id'] = $getId->id;
//        }
//       $args = array_diff_key($args, array_flip(['goal_id']));
//        \Illuminate\Support\Facades\Log::channel('single')->info('$args', [$args]);
        return tap(JapanesePost::findOrFail($args["id"]))
        ->update($args);
    }

    public function deleteJapanesePost($args)
    {
        $japanesePost = JapanesePost::find($args['id']);
        return $japanesePost->delete();
    }

    public function detailJapanesePost($args){
        if(isset($args['goal_id'])){
            $japanesePost = JapanesePost::Where('goal_id', $args['goal_id'])->first();
        }
        else{
            $japanesePost = JapanesePost::find($args['id']);
        }
        return $japanesePost;
    }

    public function myJapanesePost(){     
        return JapanesePost::where('user_id',Auth::id())->get();
    }
    public function otherJapanesePost($args)
    {
        $userId = Auth::id();
        $japanesePost = JapanesePost::whereNotIn('user_id', [$userId])->where('goal_id', $args['goal_id'])->get();
        return $japanesePost;
    }
}