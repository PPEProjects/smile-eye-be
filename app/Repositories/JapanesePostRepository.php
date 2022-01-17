<?php

namespace App\Repositories;
use App\Models\JapaneseLearn;
use App\Models\User;
use App\Models\Goal;
use App\Models\JapaneseGoal;
use App\Models\JapanesePost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use ppeCore\dvtinh\Services\AttachmentService;

use function PHPSTORM_META\map;

class JapanesePostRepository{


    private $attachment_service;

    public function __construct(
   
        AttachmentService $attachment_service
    ) {
        $this->attachment_service = $attachment_service;

    }

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
        $userId = Auth::id();
        if(isset($args['goal_id'])){
            $japanesePost = JapanesePost::where('user_id', $userId)->where('goal_id', $args['goal_id'])->first();
        }
        else{
            $japanesePost = JapanesePost::find($args['id']);
        }
        return $japanesePost;
    }

    public function myJapanesePost(){     
        $japanesePost = JapanesePost::where('user_id',Auth::id())->get();
        $japanesePost = $japanesePost->map(function($post){
            $user = $this->attachment_service->mappingAvatarBackgroud($post->user);
            $post->user = $user;
            return $post;
        });
        return $japanesePost;
    }
    public function otherJapanesePost($args)
    {
        $userId = Auth::id();
        $japanesePost = JapanesePost::whereNotIn('user_id', [$userId])->where('goal_id', $args['goal_id'])->get();
        return $japanesePost;
    }
}