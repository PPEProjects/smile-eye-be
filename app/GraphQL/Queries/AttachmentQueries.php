<?php
namespace App\GraphQL\Queries ;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\AttachmentService;


class AttachmentQueries
{
    private $attachmentService ;
    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
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
}