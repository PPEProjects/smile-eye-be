<?php

namespace App\GraphQL\Mutations;


use App\Models\JapaneseLearn;
use App\Repositories\FriendGroupRepository;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

class FriendGroupMutations{
    private $friend_group_repository;
    public function __construct(FriendGroupRepository $friend_group_repository)
    {
      $this->friend_group_repository = $friend_group_repository;
     }

    public function createfriendGroups($_,array $args){
            return $this->friend_group_repository->createfriendGroups($args);
    }
    public function updatefriendGroups($_,array $args){
        return $this->friend_group_repository->updatefriendGroups($args);
    
    }
    public function deletefriendGroups($_,array $args){
        return $this->friend_group_repository->deletefriendGroups($args);
    }
    
}