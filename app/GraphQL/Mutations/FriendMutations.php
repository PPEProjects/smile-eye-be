<?php
namespace App\GraphQL\Mutations;


use App\Models\Friend;
use App\Repositories\FriendRepository;
use Illuminate\Support\Facades\Auth;
use mysql_xdevapi\Exception;
use phpDocumentor\Reflection\Types\Boolean;
use PhpParser\Node\Scalar\String_;

class FriendMutations
{

    private $friend_repository;
    public function __construct(FriendRepository $FriendRepository)
    {
        $this->friend_repository = $FriendRepository;
    }

    public function createFriend($_, array $args)
    {
        return $this->friend_repository->createFriend(Auth::id(), $args['user_id_friend'], @$args['status']);
    }


    public function updateFriend($_, array $args)
    {
        return $this->friend_repository->updateFriend($args);

    }
    public function acceptFriend($_, array $args)
    {
        return tap(Friend::find($args['id']))->update(['status'=>'accept']);
//        return $this->friend_repository->updateFriend($args);
//        $args = array_diff_key($args, array_flip(['directive']));
//        $query = Friend::findOrFail($args['id']);
//        $args['status'] = 'accept';
//        dd($query);
//        $update = tap($query)->update($args);
//        return $update;
    }


    public function deleteFriend($_, array $args):bool
    {
        return $this->friend_repository->deleteFriend(Auth::id(), $args['user_id_friend']);
    }

}
