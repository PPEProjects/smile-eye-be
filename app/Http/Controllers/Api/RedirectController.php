<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\JapaneseGoal;
use App\Models\JapaneseLearn;

class RedirectController extends Controller
{
    //
    public function autoplay(Request $request ){
        $url = 'https://smileeye.edu.vn/m/';
        $listGoals = Goal::where('root_id',$request->root_id)
                    ->orderByRaw('-`index` DESC')
                    ->get();
        $children = $listGoals->pluck('id')->toArray();
        $japaneseLearn = JapaneseLearn::where('user_id', $request->user_id)
                    ->whereIn('goal_id', @$children ?? [])
                    ->get();
        $getGoalIds = $japaneseLearn->pluck('goal_id')->toArray();
        $nextGoal = JapaneseGoal::where('goal_id', end($getGoalIds))
                    ->first();
        $type = @$nextGoal->type ?? '';
        switch ($type) {
            case 'communication':
                $link = $url."CommunicationBlock_V2?goal_id=".$nextGoal->goal_id;
                break;
            case 'watch_video':
                $link = $url."ShowWatchVideo_V3?goal_id=".$nextGoal->goal_id;
                break;
            case 'vocabulary_grammar':
                $link = $url."WordGrammar_V2?goal_id=".$nextGoal->goal_id;
                break;
            case 'post':
                $link = $url."WatchPost_V5?goal_id=".$nextGoal->goal_id;
                break;
            case 'watch_diary':
                $link = $url."DiaryAndShare?goal_id=".$nextGoal->goal_id;
                break;
            case 'sing_with_friend':
                $link = $url."WatchSingingWithFriend_V3?goal_id=".$nextGoal->goal_id;
                break;
            case 'alphabet':
                $link = $url."LearnLetters_V2?goal_id=".$nextGoal->goal_id;
                break;
            case 'kanji':
                $link = $url."LearnKanjis_V7?goal_id=".$nextGoal->goal_id;
                break;break;
            default:
                $link = $url;
                break;
        }
        return redirect($link);
    }
}
