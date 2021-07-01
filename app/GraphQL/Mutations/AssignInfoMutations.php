<?php
namespace App\GraphQL\Mutations;

use App\Models\AssignInfo;
use App\Models\Comment;
use App\Models\ContestInfo;
use Illuminate\Support\Facades\Auth;
use App\Repositories\AssignInfoRepository;

class AssignInfoMutations
{
    private $assign_info_repository ;
    public function __construct(AssignInfoRepository $assign_info_repository)
    {
        $this->assign_info_repository = $assign_info_repository;

    }
//    private $attachment_repository;
//    public function __construct(AttachmentRepository $attachment_repository)
//    {
//        $this->attachment_repository = $attachment_repository;
//    }
    public function createAssignInfo($_, array $args)
    {
        $args['user_id'] = Auth::id();
        return AssignInfo::create($args);
    }
    public function updateAssignInfo($_, array $args)
    {
        return $this->assign_info_repository->updateAssignInfo($args);
    }
    public function deleteAssignInfo($_, array $args):bool
    {
        $cm = AssignInfo::find($args['id']);
        return $cm->delete();
    }

}
