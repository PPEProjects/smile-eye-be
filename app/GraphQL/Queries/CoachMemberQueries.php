<?php

namespace App\GraphQL\Queries;

use App\Repositories\CoachMemberRepository;

class CoachMemberQueries
{
    private $coach_member_repository;

    public function __construct(CoachMemberRepository $coach_member_repository)
    {
        $this->coach_member_repository = $coach_member_repository;
    }


    public function myListCoachMembers($_, array $args)
    {
        return $this->coach_member_repository->myListCoachMembers($args);
    }

}