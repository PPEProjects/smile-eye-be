<?php

namespace App\Repositories;

use App\Models\AssignInfo;
use App\Models\GeneralInfo;
use Carbon\Carbon;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\MediaService;

class AssignInfoRepository
{
    public function updateAssignInfo($args)
    {
        $args = array_diff_key($args, array_flip(['directive']));
        $args['user_id'] = Auth::id();
        $update = tap(AssignInfo::findOrFail($args["id"]))
            ->update($args);
        return $update;

    }
}
