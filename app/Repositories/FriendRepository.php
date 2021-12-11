<?php

namespace App\Repositories;

use App\Models\Friend;
use App\Models\Goal;
use App\Models\User;
use ppeCore\dvtinh\Services\AttachmentService;

class FriendRepository
{
    private $attachment_service;
    private $notification_repository;

    public function __construct(
        UserRepository $UserRepository,
        GoalRepository $GoalRepository,
        AttachmentService $attachment_service,
        NotificationRepository $notification_repository
    ) {
        $this->user_repository = $UserRepository;
        $this->attachment_service = $attachment_service;
        $this->goal_repository = $GoalRepository;
        $this->notification_repository = $notification_repository;
    }

    public function getByNameStatus($userId, $name=null, $status = null)
    {
        $userFriends = Friend::whereRaw("user_id={$userId}")->get()->keyBy('user_id_friend');
        $fIds = $userFriends->pluck('user_id_friend');
        $users1 = $this->user_repository->getByIds($fIds);

        $friendFriends = Friend::whereRaw("user_id_friend={$userId}")->get()->keyBy('user_id');
        $fIds = $friendFriends->pluck('user_id');
        $users2 = $this->user_repository->getByIds($fIds);
        $friends = $userFriends->toArray() + $friendFriends->toArray();
        $users = $users1->merge($users2);

        if($name){
            $users = $users->filter(function ($user) use ($name) {
                return false !== stristr($user->name, $name);
            });
        }

        $users = $users->map(function($user) use($friends){
            $friend = @$friends[$user->id];
            $user->friend = $friend;
            $user->friend_status = @$friend['status'];
            return $user;
        });
        $users = $users->map(function($user) {
            $user = $this->attachment_service->mappingAvatarBackgroud($user);
            return $user;
        });
        if($status)
            $users = $users->where('friend_status', $status);
        return $users;
    }

    public function pendFriend($userId, $status = null)
    {
        $friendFriends = Friend::whereRaw("user_id_friend={$userId} AND status = 'pending'")->get()->keyBy('user_id');
        $fIds = $friendFriends->pluck('user_id');

        $users = $this->user_repository->getByIds($fIds);
        $friends =$friendFriends->toArray();

        $mutualFriend = $this->mutualFriends($userId, $fIds, $fIds);

        $users = $users->map(function($user) use($friends, $mutualFriend){
            $friend = @$friends[$user->id];
            $user->friend = $friend;
            $user->number_mutual = count($mutualFriend[$user->id]);
            $user->mutual_friend = $mutualFriend[$user->id];
            $user->friend_status = @$friend['status'];
            return $user;
        });
        $user = $users->map(function ($u){
            $u = $this->attachment_service->mappingAvatarBackgroud($u);
            return $u;
        });

        $sortUsers = $users->sortByDESC(function ($item, $key){
            return $item->friend["id"];
        });
        return $sortUsers;
    }

    public function recommentFriends($userId, $name = null)
    {
        $userFriends = Friend::whereRaw("user_id={$userId}")->get()->keyBy('user_id_friend');
        $fIds1 = $userFriends->pluck('user_id_friend');

        $friendFriends = Friend::whereRaw("user_id_friend={$userId}")->get()->keyBy('user_id');
        $fIds2 = $friendFriends->pluck('user_id');
        $fIds = $fIds1->merge($fIds2);
        $fIdsArr = $fIds;
        $fIdsArr[] = $userId;
        //$users = $this->user_repository->getWithoutIds($fIdsArr->toArray());
        $users = User::whereNotIn('id', @$fIdsArr ?? []);
        if($name){
            $users = $users->where('name', 'LIKE', '%'.$name.'%')->get();
        }
        else
        {
            $users = $users->paginate('100', ['*'], 'page', '1');
        }
        $idFriendNotYet = $users->pluck('id');

        $getIdGoal = $users->map(function ($user)  {
                $goal = Goal::Where('user_id', $user->id);
                $fid[$user->id] = $goal->pluck('id');
                return $fid;
        });
        $idGoals = [];
       foreach ($getIdGoal as $value)
       {
           foreach ($value as $k => $v){
               $idGoals[$k] = $v;
           }
       }
        $idCompare = [];
        foreach ($users as $key){
            $idCompare[] = $key->id;
        }
        $mutualFriend = $this->mutualFriends($userId, $idFriendNotYet, $idCompare);

        $friends = $userFriends->toArray() + $friendFriends->toArray();

        
        $users = $users->map(function($user) use($friends, $mutualFriend, $idGoals){
            $friend = @$friends[$user->id];
            $user->number_mutual = count($mutualFriend[$user->id]);
            $user->number_goals = count($idGoals[$user->id]);
            $user->mutual_friend = $mutualFriend[$user->id];
            $user->friend = $friend;
            $user->friend_status = @$friend['status'];
            return $user;
        });

        if (!empty($fIds))
        {
            $sortUsers =  $users->sortByDesc("number_mutual");
        }
        else{
            $sortUsers = $users->sortByDesc("number_goals");
        }
        $users = $users->map(function ($user){
            $user = $this->attachment_service->mappingAvatarBackgroud($user);
            return $user;
        });
        return $users;
    }

    public function mutualFriends($userId, $idYourFriend, $idCompare){

        $myFriend = Friend::whereRaw("user_id = {$userId} AND status like 'accept' 
                            OR user_id_friend = {$userId} AND status like 'accept'")->get();
        //get id my friend
        $fIDmf1 = $myFriend->pluck('user_id');
        $fIDmf2 = $myFriend->pluck('user_id_friend');
        $idMyFriends = ($fIDmf1->merge($fIDmf2))->toArray();

        $yourFriends = $idYourFriend->map(function ($id) {
            $query = Friend::whereRaw("user_id = {$id} AND status like 'accept' 
                            OR user_id_friend = {$id} AND status like 'accept'");
            //get id friends
            $fid1 = $query->pluck('user_id');
            $fid2 = $query->pluck('user_id_friend');
            $fid[$id] = ($fid1->merge($fid2))->toArray();
            return $fid;
        });
        foreach($yourFriends as $value)
        {
            foreach($value as $k => $v)
            {
               $ids = array_diff($v, [$k]);
                $filter[$k] = array_intersect($ids, $idMyFriends );
            }
        }
        $mutualFriends = [];
        foreach ($idCompare as $key){
            $mutualFriends[$key] = $this->user_repository->getByIds($filter[$key])->toArray();
        }
        return $mutualFriends;
    }

    public function createFriend($userId, $userIdFriend, $status)
    {
        $friend = Friend::whereRaw("user_id={$userId} AND user_id_friend={$userIdFriend} OR user_id={$userIdFriend} AND user_id_friend={$userId}");
        if (!$friend->first()) {
            $status = 'pending';
            if ($userIdFriend == $userId){
                return false;
            }
            $update = Friend::create([
                'user_id'        => $userId,
                'user_id_friend' => $userIdFriend,
                'status'         => $status,
            ]);
            $this->notification_repository->saveNotification("friend",$update->id,$update);
        } else {
            $fOld = $friend->first();
            if ($status == 'pending') return false;
            if($fOld->user_id == $userId && $status=='accept') return false;
            $update = $friend->update(['status' => $status]);
            $fOld->status = "accept";

            $this->notification_repository->saveNotification("friend",@$fOld->id,$fOld);
        }
        return (bool)$update;
    }

    public function updateFriend($args)
    {
        $args = array_diff_key($args, array_flip(['directive']));
            $query =  Friend::findOrFail($args['id']);
            $getStatus = $query->toArray();
            $status = $getStatus["status"];
            if ($status == 'accept'){
                $update = tap($query)->update($args);
                return  $update;
            } return null;
    }

    public function deleteFriend($userId, $userIdFriend)
    {
        return Friend::whereRaw("user_id={$userId} AND user_id_friend={$userIdFriend} OR user_id={$userIdFriend} AND user_id_friend={$userId}")
            ->delete();
    }

    public  function friendAndGoal($userId, $name=null, $status = null){

        $userFriendsquery = Friend::whereRaw("user_id={$userId}")->get();
        $userFriends = $userFriendsquery->keyBy('user_id_friend');

        $fIds = $userFriends->pluck('user_id_friend');
        $users1 = $this->user_repository->getByIds($fIds);

        $fgoalIds = $userFriendsquery->pluck('goal_ids')->flatten();
        $goals1 = $this->goal_repository->getByIds($fgoalIds)->keyBy('id');

        $friendFriendsquery= Friend::whereRaw("user_id_friend={$userId}")->get();
        $friendFriends = $friendFriendsquery->keyBy('user_id');

        $fIds = $friendFriends->keyBy('user_id_friend')->pluck('user_id');
        $users2 = $this->user_repository->getByIds($fIds);

        $fgoalIds = $friendFriendsquery->pluck('goal_ids')->flatten();
        $goals2 = $this->goal_repository->getByIds($fgoalIds)->keyBy('id');

        $friends = $userFriends->toArray()  + $friendFriends->toArray();

        $goals = $goals1->toArray() + $goals2->toArray();

        $users = $users1->merge($users2);

        if($name){
            $users = $users->filter(function ($user) use ($name) {
                return false !== stristr($user->name, $name);
            });
        }

        $users = $users->map(function($user) use($friends, $goals){
            $friendId = $friends[$user->id]["id"];
            $query = Friend::where('id', $friendId);
            $getGoal = $query->pluck('goal_ids')->flatten();
            $f= $getGoal->map(function ($id) use ($goals){
                return @$goals[$id];
            });
            $friend = $friends[$user->id];
            $friend["goal_ids"] = $f->toArray();
            $user->friend = $friend;
            $user->friend_status = @$friend['status'];
            return $user;
        });

        if($status) {
            $users = $users->where('friend_status', $status);
        }

        return $users;
    }
    public function searchPeople($userId, $name = null){
        $myFriends = $this->getByNameStatus($userId)->sortBy("friend_status");
        $pendFriends = $this->pendFriend($userId);
        $listFriends = $myFriends->concat($pendFriends);
        if ($name) {
           $searchPeople = User::where('name', 'like', '%'.$name.'%')->get();
        }
        $recommentFriends =  @$searchPeople ?? $this->recommentFriends($userId);

        $people = $listFriends->concat($recommentFriends);
        if($name){
            $people = $people->filter(function ($user) use ($name) {
                return false !== stristr($user->name, $name);
            });
        }

        return $people;
    }
}
