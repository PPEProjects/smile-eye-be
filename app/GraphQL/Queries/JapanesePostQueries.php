<?php

namespace App\GraphQL\Queries;

use App\Models\JapanesePost;
use App\Repositories\JapanesePostRepository;
use Illuminate\Support\Facades\Auth;

class JapanesePostQueries
{
    private $japanese_post_repository;

    public function __construct(JapanesePostRepository $japanese_post_repository)
    {
        $this->japanese_post_repository = $japanese_post_repository;
    }

    public function detailJapanesePost($_, array $args)
    {
        return $this->japanese_post_repository->detailJapanesePost($args);
    }

    public function myJapanesePost($_, array $args)
    {
        return $this->japanese_post_repository->myJapanesePost();
    }

    public function otherJapanesePost($_, array $args)
    {
        return $this->japanese_post_repository->otherJapanesePost($args);
    }

    public function listJapanesePost($_, array $args)
    {
//        $posts = JapanesePost::selectRaw("id, user_id, CASE
//        $posts = JapanesePost::selectRaw("*, CASE
//                WHEN user_id = ".Auth::id()." THEN 1
//                ELSE 0 END AS pin_index")
//            ->where('goal_id', $args['goal_id'])
//            ->orderBy('pin_index', 'desc')
//            ->get();
        $posts = JapanesePost::selectRaw("*")
            ->where('goal_id', $args['goal_id'])
            ->orderBy('updated_at', 'desc')
            ->get();
        return $posts;
    }

    public function listJapanesePostsByGoalRoot($_, array $args){
        return $this->japanese_post_repository->listJapanesePostsByGoalRoot($args);
    }
}