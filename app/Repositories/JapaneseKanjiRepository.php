<?php

namespace App\Repositories;

use App\Models\JapaneseKanji;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class JapaneseKanjiRepository{

    public function createJapaneseKanji($args)
    {
        $args['user_id'] = Auth::id();
        return JapaneseKanji::create($args);
    }
    public function upsertJapaneseKanji($args)
    {
        $args['user_id'] = Auth::id();
        $kanji = JapaneseKanji::updateOrCreate(
            ['user_id' => $args['user_id'], 'name' => $args['name']],
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

    public function detailJapaneseKanji($args){
        return JapaneseKanji::find($args['id']);
    }

    public function myJapaneseKanji(){     
        return JapaneseKanji::where('user_id',Auth::id())->get();
    }
    public function vocabularyJapaneseKanji(){     
        return JapaneseKanji::where('name','like','vocabulary')->first();
    }
}