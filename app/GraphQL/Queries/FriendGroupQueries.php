<?php

namespace App\GraphQL\Queries;

use App\Repositories\FriendGroupRepository;
use Illuminate\Support\Facades\Auth;

class FriendGroupQueries
{
  private $friend_group_repository;
  public function __construct(FriendGroupRepository $friend_group_repository)
  {
      $this->friend_group_repository = $friend_group_repository;
  }
 public function myFriendGroups($_,array $args){
        return $this->friend_group_repository->myFriendGroups();
 }
 public function detailFriendGroups($_,array $args){
  return $this->friend_group_repository->detailFriendGroups($args);
 }
 
}