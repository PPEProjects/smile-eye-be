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
        return tap(JapanesePost::findOrFail($args["id"]))
        ->update($args);
    }

    public function deleteJapanesePost($args)
    {
        $japanesePost = JapanesePost::find($args['id']);
        return $japanesePost->delete();
    }

    public function detailJapanesePost($args){
        return JapanesePost::find($args['id']);
    }

    public function myJapanesePost(){     
        return JapanesePost::where('user_id',Auth::id())->get();
    }
}