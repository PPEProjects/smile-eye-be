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
    public function updatePublishInfo($args)
    {
        $args = array_diff_key($args, array_flip(['directive']));
        $args['user_id'] = Auth::id();
        $update = tap(PublishInfo::findOrFail($args["id"]))
            ->update($args);
        return $update;

    }
}
