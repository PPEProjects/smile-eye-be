<?php
namespace App\GraphQL\Mutations;

use App\Models\Comment;
use App\Repositories\CommentRepository;
use App\Repositories\NotificationRepository;

class CommentMutations
{
    private $comment_repository ;
    private $notification_repository;
    public function __construct(
    CommentRepository $comment_repository,
    NotificationRepository $notificationRepository
    )
    {
        $this->comment_repository = $comment_repository;
        $this->notification_repository = $notificationRepository;

    }
    public function createComment($_, array $args)
    {
       $comment = $this->comment_repository->createComment($args);
       $this->notification_repository->saveNotification('comment', $comment['id'], $comment);

       return $comment;

    }
    public function updateComment($_, array $args)
    {
        return $this->comment_repository->updateComment($args);
    }
    public function deleteComment($_, array $args):bool
    {
        $cm = Comment::find($args['id']);
        return $cm->delete();
    }

}
