<?php

namespace App\Repositories;


use App\Models\Attachment;
use App\Models\Comment;
use App\Models\GeneralInfo;
use App\Models\Goal;
use App\Models\Task;
use App\Models\Todolist;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\AttachmentService;
use App\Repositories\GeneralInfoRepository;

class CommentRepository
{
    private $attachment_service ;
    private $generalInfo_repository;
    public function __construct(AttachmentService $attachment_service,
        GeneralInfoRepository $generalInfo_repository
    )
    {
        $this->attachment_service = $attachment_service;
        $this->generalInfo_repository = $generalInfo_repository;
    }

    public function createComment(array $args)
    {
        $args['user_id'] = Auth::id();
        return Comment::create($args);
    }
    public function updateComment($args){
        $args = array_diff_key($args, array_flip(['directive']));
        $update = tap(Comment::findOrFail($args["id"]))
            ->update($args);
        return $update;
    }
    public function getMyComment($comments){
        $comments= $this->attachment_service->mappingAttachments($comments);
        return $comments;
    }
    public function getDetailComment($args){
        $comments = Comment::where('id', $args['id'])->first();
        $generalInfo = GeneralInfo::where("id",$comments->general_id)->first();
        $detail = collect();
        if($generalInfo->goal_id){
            $detail = Goal::where("id",$generalInfo->goal_id)->first();
        }else if($generalInfo->todolist_id){
            $detail = Todolist::where("id",$generalInfo->todolist_id)->first();
        }else if($generalInfo->task_id){
            $detail = Task::where("id",$generalInfo->task_id)->first();
        }
        $detail->generral_info = $generalInfo;
        $comments->detail = $detail;
        $comments= $this->attachment_service->mappingAttachment($comments);
        return $comments;
    }
    public function getUserCommentByGeneralId($generalId){
        $comments = Comment::where('general_id', $generalId);
        $user_ids = $comments
            ->groupBy("user_id")
            ->pluck("user_id");
        return $user_ids;
    }
    public function getCommentsByGeneralId($generalId){
        $comments = Comment::where('general_id', $generalId);
        $user_ids = $comments
            ->pluck("user_id");
        $users = User::whereIn("id",$user_ids)->get();
        $comments = $comments
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($cmt) use($users) {
            $user = $users->where("id",$cmt->user_id)->first();
            $user = $this->attachment_service->mappingAvatarBackgroud($user);

            $cmt->user = $user;
            return $cmt;
        });
        $comments = $this->attachment_service->mappingAttachments($comments);
        return $comments;
    }
    public function getParentCommentsByGeneralId($generalId){
        $comments = Comment::orderBy('id', 'desc');
        $comments = $comments->where('general_id', $generalId)
                             ->where('parent_id', NULL)
                             ->get();
        $comments = $this->attachment_service->mappingAttachments($comments);
        $comments = $comments->map(function ($comment) use ($comments) {
            $acttachmentId = $comment->user->toArray()['attachment_id'];
            $attachment = Attachment::where('id',$acttachmentId)->first();
            if ($attachment) {
                [$thumb, $file] = $this->attachment_service->getThumbFile($attachment->file_type, $attachment->file);
                $attachment->thumb = $thumb;
            }
            $comment->user->attachment = $attachment;
            return $comment;
        });
        return $comments;
    }
}
