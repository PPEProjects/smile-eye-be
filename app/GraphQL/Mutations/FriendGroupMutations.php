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

    public function createFriendGroups($_,array $args){
            return $this->friend_group_repository->createFriendGroups($args);
    }
    public function updateFriendGroups($_,array $args){
        return $this->friend_group_repository->updateFriendGroups($args);
    
    }
    public function deleteFriendGroups($_,array $args){
        return $this->friend_group_repository->deleteFriendGroups($args);
    }
    public function deleteMemberFriendGroups($_,array $args){
        return $this->friend_group_repository->deleteMemberFriendGroups($args);
    }
    public function addMemberFriendGroups($_,array $args){
        return $this->friend_group_repository->addMemberFriendGroups($args);
    }
}