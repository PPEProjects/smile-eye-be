<?php
namespace App\GraphQL\Queries ;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use ppeCore\dvtinh\Services\AttachmentService;


class ContestInfoQueries
{
    private $attachmentService ;
    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }
    public function my_ContestInfos()
    {
        $id = Auth::id();
        return User::find($id)->contests;
    }
}