<?php
namespace App\GraphQL\Queries ;

use App\Models\Friend;
use App\Models\User;
use App\Repositories\FriendRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\AttachmentService;

class FriendQueries
{
    private $media_service;

    public function __construct(
        FriendRepository $FriendRepository,
        AttachmentService $MediaService,
        UserRepository $UserRepository)
    {
        $this->friend_repository = $FriendRepository;
        $this->attachment_service = $MediaService;
        $this->user_repository = $UserRepository;
    }

    public function my_friends($_, array $args)
    {
        $friends = $this->friend_repository->getByNameStatus(Auth::id(), @$args['name'], @$args['status']);
        return $friends;
    }

    public function pendFriend($_, array $args)
    {
        return $this->friend_repository->getByNameStatus(Auth::id(), @$args['name'], "pending");
    }

    public function recommentFriends($_, array $args)
    {
        return $this->friend_repository->recommentFriends(Auth::id(), @$args['name']);
    }
    public function friendAndGoal($_, array $args)
    {
        return $this->friend_repository->friendAndGoal(Auth::id(), @$args['name'], @$args['status']);
    }
    public function searchPeople($_, array $args)
    {
        return $this->friend_repository->searchPeople(Auth::id(), @$args['name']);
    }

}