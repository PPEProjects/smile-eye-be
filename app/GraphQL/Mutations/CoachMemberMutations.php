<?php
namespace App\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use App\Repositories\CoachMemberRepository;

class CoachMemberMutations
{
    private $coach_member_repository ;
    public function __construct(CoachMemberRepository $coach_member_repository)
    {
        $this->coach_member_repository = $coach_member_repository;

    }

    public function createCoachMember($_, array $args)
    {
        return $this->coach_member_repository->createCoachMember($args);
    }
    public function addCoachMember($_, array $args)
    {
        return $this->coach_member_repository->addCoachMember($args);
    }
    public function updateCoachMember($_, array $args)
    {
        return $this->coach_member_repository->updateCoachMember($args);
    }
    public function deleteCoachMember($_, array $args)
    {
        return $this->coach_member_repository->deleteCoachMember($args);
    }

}
