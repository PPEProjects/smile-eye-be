<?php
namespace App\GraphQL\Mutations;

use App\Models\Log;
use App\Repositories\LogRepository;
use Illuminate\Support\Facades\Auth;

class LogMutations
{
    private $log_repository;
    public function __construct(LogRepository $LogRepository)
    {
        $this->log_repository = $LogRepository;
    }

    public function createLog($_, array $args): Log
    {
        $args['user_id'] = Auth::id();
        return Log::create($args);
    }
    public function deleteLog($_, array $args):bool
    {
        $log = Log::find($args['id']);
        return $log->delete();
    }

    public function updateLog($_,array $args):Log
    {
        return $this->log_repository->updateLog($args) ;
    }
}