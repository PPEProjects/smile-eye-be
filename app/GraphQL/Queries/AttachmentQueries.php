<?php
namespace App\GraphQL\Queries ;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\AttachmentService;
use App\Repositories\AttachmentRepository;


class AttachmentQueries
{
    private $attachmentService ;
    private $attachment_repository ;
    public function __construct(AttachmentService $attachmentService,
        AttachmentRepository $attachment_repository
    )
    {
        $this->attachmentService = $attachmentService;
        $this->attachment_repository = $attachment_repository;
    }

    public function my_attachment()
    {
        $id = Auth::id();

        $attachments =  User::find($id)->attachments;
        $attachments = $attachments->map(function ($at){
            [$thumb, $file] = $this->attachmentService->getThumbFile($at->file_type,$at->file);
            $at->thumb = $thumb;
            return $at;
        });
        return $attachments;
    }
    public function beforDelete($_,array $args){
        return $this->attachment_repository->beforDelete($args);
    }
}