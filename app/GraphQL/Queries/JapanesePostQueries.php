<?php

namespace App\GraphQL\Queries;

use App\Models\JapanesePost;
use App\Repositories\JapanesePostRepository;

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
//        return $this->japanese_post_repository->otherJapanesePost($args);
        return JapanesePost::where('goal_id', $args['goal_id'])
            ->get();
    }
}