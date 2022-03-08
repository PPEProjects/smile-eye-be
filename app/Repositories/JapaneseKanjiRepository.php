<?php

namespace App\Repositories;

use App\Models\JapaneseKanji;
use GraphQL\Error\Error;
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

        return true;
    }

    public function upsertJapaneseKanji($args)
    {
        $args['user_id'] = Auth::id();
        if (isset($args['name'])) {
            // if(preg_match('/[\x{4E00}-\x{9FBF}]/u', $args['name']) <= 0){
            //     throw new Error("This is not a Kanji. Please input Kanji letter!");
            // }   

            if (strlen($args['name']) > 3) {
                throw new Error("Please input 1 Kanji letter!");
            }
        }
        if (@$args['more'] == []) {
            $args = array_diff_key($args, array_flip(['more']));
        }
        $checkDataKanji = ['name' => @$args['name']];
        if (isset($args['id'])) {
            $checkDataKanji = ['id' => $args['id']];
            if (!is_numeric($args['id'])) {
                $checkDataKanji = ['name' => $args['name']];
                unset($args['id']);
            }
        }
        $kanji = JapaneseKanji::updateOrCreate($checkDataKanji, $args);
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
            $japaneseKanji = [];
            foreach ($args['ids'] as $id) {
                $japaneseKanji[] = JapaneseKanji::find($id);
            }
            return $japaneseKanji;
        }
        $japaneseKanji = JapaneseKanji::all();
        return $japaneseKanji;
    }
}