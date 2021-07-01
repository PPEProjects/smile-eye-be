<?php

namespace App\Repositories;


use App\Models\Log;

class LogRepository
{
    public function updateLog($args){
        $args = array_diff_key($args, array_flip(['directive']));
        $update = tap(Log::findOrFail($args["id"]))
            ->update($args);
        return $update;
    }
}
