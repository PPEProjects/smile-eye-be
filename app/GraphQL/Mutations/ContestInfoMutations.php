<?php
namespace App\GraphQL\Mutations;

use App\Models\Comment;
use App\Models\ContestInfo;
use Illuminate\Support\Facades\Auth;
use App\Repositories\ContestInfoRepository;

class ContestInfoMutations
{
    private $contest_into_repository ;
    public function __construct(ContestInfoRepository $contest_into_repository)
    {
        $this->contest_into_repository = $contest_into_repository;

    }
//    private $attachment_repository;
//    public function __construct(AttachmentRepository $attachment_repository)
//    {
//        $this->attachment_repository = $attachment_repository;
//    }
    public function createContestInfo($_, array $args)
    {
        $args['user_id'] = Auth::id();
        return ContestInfo::create($args);
    }
    public function updateContestInfo($_, array $args)
    {
        return $this->contest_into_repository->updateContestInfo($args);
    }
    public function deleteContestInfo($_, array $args):bool
    {
        $cm = ContestInfo::find($args['id']);
        return $cm->delete();
    }

}
