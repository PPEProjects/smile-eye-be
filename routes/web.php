<?php
// test git
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return response()->json([
        'status' => true,
        'path' => 'homepage'
    ]);
});
Route::get('/file', function () {
//    echo ini_get('post_max_size');
//    ini_set('memory_limit','10240M');
//    echo ini_get('post_max_size');
    echo phpinfo();
});
Route::get('get-token', function (Request $request) {
    $user = \Illuminate\Support\Facades\Auth::id();
    return $user;
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Auth::routes(['verify' => true]);
///////////////////////////////////////////////
Route::get('/sign-in/github', function () {
//    dd(Socialite::driver('github'));
    return Socialite::driver('github')->redirect();
});


Route::get('/sign-in/github/redirect', function () {
    $user = Socialite::driver('github')->user();
    dd($user);
});
//////////////////////////////////////////////GOOGLE
Route::get('/sign-in/google', function () {
//    dd(Socialite::driver('github'));
    return Socialite::driver('google')->redirect();
});


Route::get('/sign-in/google/redirect', function () {
    $user = Socialite::driver('google')->user();
    dd($user);
});
/////////////////////////////////////////////FACEBOOK
Route::get('/sign-in/facebook', function () {
//    dd(Socialite::driver('github'));
    return Socialite::driver('facebook')->redirect();
});


Route::get('/sign-in/facebook/redirect', function () {
    $user = Socialite::driver('facebook')->user();
    dd($user);
});

Route::get('/counter', function () {
    return view('counter');
});
Route::get('/pusher', function () {
    event(new \App\Events\NotificationMessage('dcmakdjfslflfjkfdllkfsl'));
    return "sended";
});
Route::get('/sender', function () {
    return view('sender');
});
Route::post('/sender', function () {
    $data = request()->data;
    event(new \App\Events\LoginMessage($data));
});


Route::get('/japanese_kanjis', function () {
    $JapaneseKanji = \App\Models\JapaneseKanji::all();
    foreach ($JapaneseKanji as $item) {
        $more = $item->more;
        $more['writing'] = [
            'radioKey' => 'image',
            'file' => @$more['img']
        ];
        $item->more = $more;
        $item->save();
    }
    dd('done');
});


Route::get('/general_infos', function () {
    $count = 0;
    $general = \App\Models\GeneralInfo::whereNotNull('attachment_ids')
        ->get()
        ->map(function ($item) use (&$count) {
            if ($item->attachments->isNotEmpty()) {
//                dd($item->toArray());
                $attachment = $item->attachments->first()->toArray();
                $attachment['file'] = "https://be-ppe.codeby.com/storage/" . $attachment['file'];

                $count += \App\Models\GeneralInfo::where('id', $item->id)->update(['storage' => $attachment]);
            }
        });

    dd('done', $count);
});


Route::get('/japanese_goals', function () {
    $query = "SELECT id, more FROM japanese_goals WHERE more like '%radioKey%';";
    $results = DB::select(DB::raw($query));
    $res = json_decode(json_encode($results), true);
    foreach ($res as $re) {
        $more = $re['more'];
        $more = preg_replace('#Audio#', 'audio', $more);
        $more = preg_replace('#Image#', 'image', $more);
        $more = preg_replace('#Video#', 'video', $more);
        $more = preg_replace('#Record#', 'record', $more);
        $more = preg_replace('#recorder#', 'record', $more);
        $more = json_decode($more, true);
        \App\Models\JapaneseGoal::where('id', $re['id'])
            ->update(['more' => $more]);
//           "UPDATE `japanese_goals` SET `more` =\"\" WHERE `japanese_goals`.`id` = 1585;"
//            dd($more);
    }
    dd('done');
});


Route::get('/update_letter', function () {
    return view('update_letter');
});
Route::post('/update_letter', function (Request $request) {
    if ($request->hasFile('LetterExample')) {
        $file = $request->LetterExample;
        $fileRootName = $file->getClientOriginalName();
        $file->move(storage_path() . "/app/public/Letter/example", $fileRootName);
    }
    if ($request->hasFile('LetterA')) {
        $file = $request->LetterA;
        $fileRootName = $file->getClientOriginalName();
        $file->move(storage_path() . "/app/public/Letter/a", $fileRootName);
    }
    return redirect('/update_letter?success');
});

Route::get('/update_watch', function (Request $request) {
    $updateCount = ['total' => 0, 'update' => 0];
    $jpGoals = \App\Models\JapaneseGoal::where('type', 'watch_video')
        ->get()
        ->map(function ($item) use (&$updateCount) {
//            $updateCount += 1;
            $item = $item->toArray();
            $skit_video = @$item['more']['skit_video'];
            if ($skit_video && !@$skit_video['video']) {
                $skit_video['video'] = $skit_video;
                $skit_video['thumb'] = $skit_video;
                $item['more']['skit_video'] = $skit_video;
//                dd($item);
                $updated = \App\Models\JapaneseGoal::where('id', $item['id'])
                    ->update(['more' => $item['more']]);
                $updateCount['update'] += $updated;
                $updateCount['total'] += 1;

            }
        });
    dd($updateCount);
//   return redirect('/update_letter?success');
});
