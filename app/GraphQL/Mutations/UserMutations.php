<?php

namespace App\GraphQL\Mutations;


use App\Models\Attachment;
use App\Models\User;
use App\Repositories\AttachmentRepository;
use Illuminate\Support\Facades\Auth;
use App\Repositories\UserRepository;
class UserMutations
{
    private $attachment_repository;
    private $userRepository;
    public function __construct(AttachmentRepository $attachment_repository, UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->attachment_repository = $attachment_repository;
    }

    public function createUser($_, array $args)
    {
        return $this->userRepository->createUser($args);
    }
    public function updateUser($_, array $args): User
    {
        return $this->userRepository->updateUser($args);
    }


    public function deleteUser($_, array $args): bool
    {
        $args['id'] = Auth::id();
        $user = User::find($args['id']);
        return $user->delete();
    }
}
