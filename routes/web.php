<?php
// test git
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

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
        'path'   => 'homepage'
    ]);
});
Route::get('/file', function () {
    echo ini_get('post_max_size');
    ini_set('memory_limit','10240M');
    echo ini_get('post_max_size');
    echo phpinfo();
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
