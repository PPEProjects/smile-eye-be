<?php

namespace App\GraphQL\Mutations;


use App\Models\JapaneseLearn;
use App\Repositories\JapaneseLearnRepository;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

class JapaneseLearnMutations{
    private $japanese_learn_repository;
    public function __construct(JapaneseLearnRepository $japanese_learn_repository)
    {
        $this->japanese_learn_repository = $japanese_learn_repository;
    }

    public function upsertJapaneseLearn($_,array $args){
            return $this->japanese_learn_repository->upsertJapaneseLearn($args);
    }
    public function updateJapaneseLearn($_,array $args){
        return $this->japanese_learn_repository->updateJapaneseLearn($args);
    
    }
    public function deleteJapaneseLearn($_,array $args){
        return $this->japanese_learn_repository->deleteJapaneseLearn($args);
    }
    
}