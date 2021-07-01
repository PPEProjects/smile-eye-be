<?php

namespace App\GraphQL\Queries;

use App\Repositories\AchieveRepository;

class AchieveQueries
{
    private $achieve_repository;

    public function __construct(AchieveRepository $achieve_repository)
    {
        $this->achieve_repository = $achieve_repository;
    }


    public function detailAchieve($_, array $args)
    {
        return $this->achieve_repository->detailAchieve($args);
    }

}