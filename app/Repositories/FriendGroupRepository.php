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
        $args['user_id'] = Auth::id();
        return FriendGroup::where('user_id',$args['user_id'])->orderBy('id', 'DESC')->get();
    }
    public function detailfriendGroups($args){
        $args['user_id'] = Auth::id();
        return FriendGroup::find($args['id']);
    }
}