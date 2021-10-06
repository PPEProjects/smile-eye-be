<?php

namespace App\Repositories;

use App\Models\FriendGroup;
use Illuminate\Support\Facades\Auth;

class FriendGroupRepository
{
    public function __construct(
        NotificationRepository $notificationRepository
    ) 
    {    
        $this->notification_repository = $notificationRepository;
    }
    public function createFriendGroups($args){
        $userId = Auth::id();
        $userInvitedIds = [];
        foreach($args['people'] as $value){
            if($value['user_id'] != $userId){
                $userInvitedIds[] = $value['user_id'];
            }
        }   
        $args['user_id'] = $userId;
        $friendGroup = FriendGroup::create($args);
        $this->notification_repository->staticNotification("friend_group", $friendGroup->id, $friendGroup, $userInvitedIds);
        return $friendGroup;
    }
    public function updateFriendGroups($args){
        $args['user_id'] = Auth::id();
        return tap(FriendGroup::findOrFail($args["id"]))->update($args);;
    }
    public function deleteFriendGroups($args){
        $friendGroup = FriendGroup::find($args['id']);
        return $friendGroup->delete();
    }
    public function checkRoleMemberFriendGroups($id, $userId, $member = null){
        $roleAdmin = $this->getfriendGroupsByRole($userId, 'admin');
        $roleAdmin = $roleAdmin->where('id', $id)->first();

        $roleMod = $this->getfriendGroupsByRole($userId, 'mod');
        $roleMod = $roleMod->where('id', $id)->first();

        $userDeleteAdmin = $this->getfriendGroupsByRole($member, 'admin')->where('id', $id)->first();
        $userDeleteMod = $this->getfriendGroupsByRole($member, 'mod')->where('id', $id)->first();
        $role = "member";
        if(isset($roleAdmin)){
            $friendGroup = $roleAdmin;
            $role = 'admin';
        }
        else if(isset($roleMod)){
            $friendGroup = $roleMod;
            $role = 'mod';
        }
        if(isset($friendGroup)){
            switch ($role) {
                case 'admin':
                    if(isset($userDeleteAdmin)) return false;
                    break;
                case 'mod':
                    if(isset($userDeleteMod) || isset($userDeleteAdmin)) return false;
                    break;
                default:
                    return false;
                    break;
            }
            return $friendGroup;
        }
        return false;
    }
    public function deleteMemberFriendGroups($args)
    {
        $userId = Auth::id();
        $friendGroup = $this->checkRoleMemberFriendGroups($args['id'], $userId, $args['user_id']);
        if($friendGroup){
            $resetMember = [];
            foreach ($friendGroup->people as $value) {
                if($value['user_id'] == $args['user_id']){
                    continue;
                }
                $resetMember[] = $value;
            }
            $updateFriendGroup = ['id'=> $args['id'], 'people' => $resetMember];
            $this->updatefriendGroups($updateFriendGroup);
        }
        return $friendGroup;
    }
    public function addMemberFriendGroups($args)
    {
        $userId = Auth::id();
        $friendGroup = $this->checkRoleMemberFriendGroups($args['id'], $userId);
        if($friendGroup)
        {
            $resetMember = [];
            foreach ($friendGroup->people as $value) {
                    $getId[] = $value['user_id'];
            }
            $userIdInvited = array_diff($args['user_ids'],$getId);
            foreach ($userIdInvited as $id) {
                $resetMember[] = ['user_id' => intval($id), 'role' => 'member'];
            }
            $people =  array_merge($friendGroup->people, $resetMember);
            $updateFriendGroup = ['id'=> $args['id'], 'people' => $people];
            $update = $this->updatefriendGroups($updateFriendGroup);
            $this->notification_repository->staticNotification("friend_group", $update->id, $update,  $userIdInvited);
        }
        return $friendGroup;
    }
    public function getfriendGroupsByRole($userId, $role){
        $role = FriendGroup::where('people', 'like', '%"user_id":'.$userId.',"role":"'.$role.'"%')->get();
        return $role;
    }
    public function myfriendGroups(){
        $userId = Auth::id();
        $myGroups = FriendGroup::where('user_id',$userId)->orderBy('id', 'DESC')->get();
        $inviteGroups = FriendGroup::where('people', 'like', '%"user_id":'.$userId.'%')
                                        ->whereNotIn('user_id', [$userId])
                                        ->orderBy('id', 'DESC')->get();
        $groups = $myGroups->merge($inviteGroups);
        return $groups;
    }
    public function searchFriendGroups($args){
        $name = @$args['name'] ?? "";
        $myGroups = $this->myfriendGroups();
        $getIdMyGroups = $myGroups->pluck('id');
        $allGroups = FriendGroup::whereNotIn('id', $getIdMyGroups)->get();
        $friendGroup = $myGroups->merge($allGroups);
        if($name != ""){
            $friendGroup = $friendGroup->filter(function ($user) use ($name) {
                return false !== stristr($user->name, $name);
            });
        }
        return $friendGroup;
    }
    public function detailfriendGroups($args){
        $args['user_id'] = Auth::id();
        return FriendGroup::find($args['id']);
    }
}