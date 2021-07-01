<?php

namespace App\Repositories;

use App\Models\ContestInfo;
use Illuminate\Support\Facades\Auth;

class ContestInfoRepository
{
    public function updateContestInfo($args)
    {
        $args = array_diff_key($args, array_flip(['directive']));
        $args['user_id'] = Auth::id();
        $update = tap(ContestInfo::findOrFail($args["id"]))
            ->update($args);
        return $update;

    }
}
