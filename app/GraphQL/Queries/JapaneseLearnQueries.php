<?php

namespace App\GraphQL\Queries;

use App\Models\JapaneseLearn;
use App\Repositories\JapaneseLearnRepository;

class JapaneseLearnQueries
{
    private $japanese_learn_repository;

    public function __construct(JapaneseLearnRepository $japanese_learn_repository)
    {
        $this->japanese_learn_repository = $japanese_learn_repository;
    }

    public function myJapaneseLearn()
    {
        return $this->japanese_learn_repository->myJapaneseLearn();
    }

    public function detailJapaneseLearn($_, array $args)
    {
        return $this->japanese_learn_repository->detailJapaneseLearn($args);
    }

    public function listUserIDJapaneseLearn($_, array $args)
    {
        $userIds = JapaneseLearn::where('goal_id', $args['goal_id'])
            ->get()
            ->pluck('user_id')
            ->unique()
            ->toArray();
        return ['user_ids' => $userIds];
    }
    public  function progressUserJapaneseLearn($_, array  $args){
        return $this->japanese_learn_repository->progressUserJapaneseLearn($args);
    }
}