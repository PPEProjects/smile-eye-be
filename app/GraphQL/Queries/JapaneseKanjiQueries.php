<?php

namespace App\GraphQL\Queries;

use App\Repositories\JapaneseKanjiRepository;
use Illuminate\Support\Facades\Auth;

class JapaneseKanjiQueries
{
  private $japanese_kanji_repository;
  public function __construct(JapaneseKanjiRepository $japanese_kanji_repository)
  {
      $this->japanese_kanji_repository = $japanese_kanji_repository;
  }
 public function myJapaneseKanji($_,array $args){
        return $this->japanese_kanji_repository->myJapaneseKanji();
 }
 public function detailJapaneseKanji($_,array $args){
  return $this->japanese_kanji_repository->detailJapaneseKanji($args);
 }
 
}