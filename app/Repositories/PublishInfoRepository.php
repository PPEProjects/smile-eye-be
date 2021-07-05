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
        $update = tap(PublishInfo::findOrFail($args["id"]))
            ->update($args);
        return $update;

    }
    public function find($useId){
      $publishInfo = PublishInfo::where('user_invite_id', $useId)->get()->keyBy("general_id");
      return $publishInfo->toArray();

    }
}
