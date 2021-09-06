<?php

namespace App\GraphQL\Queries;

use App\Models\Goal;
use App\Models\JapanesePost;
use App\Models\User;
use App\Repositories\JapanesePostRepository;
use Illuminate\Support\Facades\Auth;

class JapanesePostQueries
{
    private $japanese_post_repository;

    public function __construct(JapanesePostRepository $japanese_post_repository)
    {
        $this->japanese_post_repository = $japanese_post_repository;
    }
  public function detailJapanesePost($_,array $args){
        return $this->japanese_post_repository->detailJapanesePost($args);
  }
  public function myJapanesePost($_,array $args){ 
        return  $this->japanese_post_repository->myJapanesePost();
    }
    public function otherJapanesePost($_,array $args)
    {
        return  $this->japanese_post_repository->otherJapanesePost($args);
    }
}