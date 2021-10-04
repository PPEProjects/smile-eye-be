<?php

namespace App\Repositories;

use App\Models\FriendGroup;
use Illuminate\Support\Facades\Auth;

class FriendGroupRepository
{
    public function createfriendGroups($args){
        $args['user_id'] = Auth::id();
        return FriendGroup::create($args);
    }
    public function updatefriendGroups($args){
        $args['user_id'] = Auth::id();
        return tap(FriendGroup::findOrFail($args["id"]))->update($args);;
    }
    public function deletefriendGroups($args){
        $friendGroup = FriendGroup::find($args['id']);
        return $friendGroup->delete();
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
    public function detailfriendGroups($args){
        $args['user_id'] = Auth::id();
        return FriendGroup::find($args['id']);
    }
}