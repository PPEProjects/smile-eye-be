<?php

namespace App\Repositories;

use App\Models\GeneralInfo;
use App\Models\PublishInfo;
use Carbon\Carbon;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\MediaService;

class PublishInfoRepository
{
    public function createPublishInfo($args){
        $publishInfo = PublishInfo::Where("general_id", $args['general_id'])->get();
        foreach($publishInfo as $value){
            if($args['user_invite_id'] == $value->user_invite_id){
                return false;
            }
        }
       return PublishInfo::create($args);
    }
    public function updatePublishInfo($args)
    {
        $args = array_diff_key($args, array_flip(['directive']));
        $args['user_id'] = Auth::id();
        if(!isset($args['id']))
        {
            $publish = PublishInfo::where('general_id',$args['general_id'])
                                    ->Where("user_invite_id", Auth::id())
                                    ->first();
            $id = $publish->id;
        }
        else
        {
           $id = $args['id'];
        }
        $update = tap(PublishInfo::findOrFail($id))
                    ->update($args);
        return $update;

    }
    public function find($generalId, $userId){
      return PublishInfo::where('user_invite_id',$userId)
                                    ->where("general_id", $generalId)
                                    ->first();

    }
}
