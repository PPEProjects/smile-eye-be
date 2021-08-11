<?php

namespace App\GraphQL\Queries;

use App\Models\Goal;
use App\Models\JapaneseLearn;
use App\Repositories\JapaneseLearnRepository;
use Illuminate\Support\Facades\Auth;

class JapaneseLearnQueries
{
  private $japanese_learn_repository;
  public function __construct(JapaneseLearnRepository $japanese_learn_repository)
  {
      $this->japanese_learn_repository = $japanese_learn_repository;
  }
 public function myJapaneseLearn(){
        return $this->japanese_learn_repository->myJapaneseLearn();
 }
 public function detailJapaneseLearn($_,array $args){
  return $this->japanese_learn_repository->detailJapaneseLearn($args);
 }
 
}