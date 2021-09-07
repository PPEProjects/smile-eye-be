<?php

namespace App\GraphQL\Mutations;


use App\Models\JapaneseLearn;
use App\Models\JapanesePost;
use App\Repositories\JapanesePostRepository;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

class JapanesePostMutations{

    private $japanese_post_repository;

    public function __construct(JapanesePostRepository $japanese_post_repository)
    {
        $this->japanese_post_repository = $japanese_post_repository;
    }
    public function createJapanesePost($_,array $args)
    {
        $args['user_id'] = Auth::id();
        return $this->japanese_post_repository->createJapanesePost($args);
    }
    public function updateJapanesePost($_,array $args)
    {
//        $args['user_id'] = Auth::id();
        return $this->japanese_post_repository->updateJapanesePost($args);
    }
    public function deleteJapanesePost($_,array $args)
    {
        return $this->japanese_post_repository->deleteJapanesePost($args);
    }
}