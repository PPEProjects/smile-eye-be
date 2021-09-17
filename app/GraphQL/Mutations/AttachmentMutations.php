<?php

namespace App\GraphQL\Mutations;


use App\Models\Attachment;
use App\Models\Challenge;
use App\Repositories\AttachmentRepository;
use Illuminate\Support\Facades\Auth;

class AttachmentMutations
{
    private $attachment_repository;

    public function __construct(AttachmentRepository $attachment_repository)
    {
        $this->attachment_repository = $attachment_repository;
    }

    public function createAttachment($_, array $args): Attachment
    {
        $args['user_id'] = Auth::id();
        return Attachment::create($args);
    }

    public function deleteAttachment($_, array $args): bool
    {
        $attachment = Attachment::find($args['id']);
        return $attachment ? $attachment->delete() : true;
    }

    public function updateAttachment($_, array $args): Attachment
    {
        return $this->attachment_repository->updateAttachment($args);
    }
}
