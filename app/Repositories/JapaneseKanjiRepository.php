<?php

namespace App\Repositories;

use App\Models\JapaneseKanji;
use Illuminate\Support\Facades\Auth;

class JapaneseKanjiRepository
{

    public function createJapaneseKanji($args)
    {
        $args['user_id'] = Auth::id();
        // return JapaneseKanji::create($args);
        $vocabulary = [];
        foreach ($args['more'] as $value) {
            $value['name'] = explode(" ", $value['name']);
            $vocabulary['name'] = current($value['name']);
            $vocabulary['more'] = array_diff_key($value, array_flip(['id', 'name']));
            JapaneseKanji::create($vocabulary);
        }
        $vocabulary = [];
        foreach ($args['more'] as $value) {
            $value['name'] = explode(" ", $value['name']);
            $vocabulary['name'] = current($value['name']);
            $vocabulary['more'] = array_diff_key($value, array_flip(['id', 'name']));
            JapaneseKanji::create($vocabulary);
        }
        return true;
    }

    public function upsertJapaneseKanji($args)
    {
        $args['user_id'] = Auth::id();
        $kanji = JapaneseKanji::updateOrCreate(
            ['id' => @$args['id']],
            $args
        );
        return $kanji;
    }

    public function updateJapaneseKanji($args)
    {
        $args['user_id'] = Auth::id();
        return tap(JapaneseKanji::findOrFail($args["id"]))->update($args);
    }

    public function deleteJapaneseKanji($args)
    {
        $japaneseKanji = JapaneseKanji::find($args['id']);
        return $japaneseKanji->delete();
    }

    public function detailJapaneseKanji($args)
    {
        return JapaneseKanji::find($args['id']);
    }

    public function myJapaneseKanji()
    {
        return JapaneseKanji::where('user_id', Auth::id())->get();
    }

    public function vocabularyJapaneseKanji()
    {
        return JapaneseKanji::all();
    }

    public function listJapaneseKanji($args)
    {
        if (!empty($args['ids'])) {
            return JapaneseKanji::whereIn('id', $args['ids'])->get();
        }
        return JapaneseKanji::all();
    }
}