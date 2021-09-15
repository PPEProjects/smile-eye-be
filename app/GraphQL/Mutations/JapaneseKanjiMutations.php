<?php

namespace App\GraphQL\Mutations;

use App\Models\JapaneseKanji;
use App\Repositories\JapaneseKanjiRepository;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

class JapaneseKanjiMutations{

    private $japanese_kanji_repository;

    public function __construct(JapaneseKanjiRepository $japanese_kanji_repository)
    {
        $this->japanese_kanji_repository = $japanese_kanji_repository;
    }
    public function createJapaneseKanji($_,array $args)
    {
       return $this->japanese_kanji_repository->createJapaneseKanji($args);
    }
    public function upsertJapaneseKanji($_,array $args)
    {
       return $this->japanese_kanji_repository->upsertJapaneseKanji($args);
    }
    public function updateJapaneseKanji($_,array $args)
    {
        return $this->japanese_kanji_repository->updateJapaneseKanji($args);
    }
    public function deleteJapaneseKanji($_,array $args)
    {
        return $this->japanese_kanji_repository->deleteJapaneseKanji($args);
    }
}