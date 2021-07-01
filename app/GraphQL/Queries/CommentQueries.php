<?php
namespace App\GraphQL\Queries ;

use App\Models\Comment;
use App\Models\User;
use App\Repositories\CommentRepository;
use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Null_;
use ppeCore\dvtinh\Services\AttachmentService;


class CommentQueries
{
    private $attachmentService ;
    private $commentRepository;
    public function __construct(
        AttachmentService $attachmentService,
        CommentRepository $commentRepository
    )
    {
        $this->attachmentService = $attachmentService;
        $this->commentRepository = $commentRepository;
    }
    protected function _buildTree(array $elements, $parentId = 0)
    {
        $branch = array();
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = self::_buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
//        dd($branch);
        return $branch;
    }
    public function myComments()
    {
        $id = Auth::id();
        $comments =  User::find($id)->comments;
        $comments = $this->commentRepository->getMyComment($comments);
        return $comments;
    }
    public function Comments($_, array $args)
    {
        $comments = $this->commentRepository->getCommentsByGeneralId($args["general_id"]);
        $commentParent = $comments->whereNull("parent_id");
        $commentChild = $comments->whereNotNull("parent_id");

        $commentParent = $commentParent->map(function ($com) use($commentChild){
                $com->children = $commentChild->where("parent_id",$com->id);
                return $com;
        });
        return $commentParent;
    }
    public function detailComment($_, array $args)
    {
        return $this->commentRepository->getDetailComment($args);
    }

}