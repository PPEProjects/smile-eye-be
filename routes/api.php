<?php

use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\LetterAttachmentController;
use App\Http\Controllers\Api\RedirectController;
use App\Services\GoogleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MomoController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//https://03766a3ff816.ngrok.io/api/auth-service?platform=google
//https://accounts.google.com/AccountChooser/signinchooser?continue=https://g.co/meet/yourmeetingname
//https://accounts.google.com/AccountChooser/signinchooser?continue=https://g.co/meet/tiennv.ppe@gmail.com

Route::prefix('/auth-service')->group(function () {
    Route::get('/', function (Request $request) {
        switch ($request->platform) {
            case 'google':
                $url = GoogleService::generateUrl();
                return redirect($url);
        }
    });
    Route::get('/handle', function (Request $request) {
        $state = json_decode($request->state, true);
        switch ($state['platform']) {
            case 'google':
                $user = GoogleService::handle($request->code);
                dd($user);
                break;
        }
    });
});


Route::prefix('/redirect')->group(function () {
    Route::get('/autoplay', [RedirectController::class, 'autoplay']);
    Route::get('/nextBlock', [RedirectController::class, 'nextBlock']);
    Route::get('/prevBlock', [RedirectController::class, 'prevBlock']);
});

Route::resource('attachment', AttachmentController::class)
    ->middleware('auth');

//Route::resource('attachment-letter/{type}', LetterAttachmentController::class)->middleware('auth');

Route::get('goals/gantt-chart/{user_id}', [GoalController::class, 'ganttChart']);


Route::prefix('/momo')->group(function () {
    Route::post('/generate-url/{type}', [MomoController::class, 'generateUrl']);
    Route::get('/callback/{type}', [MomoController::class, 'callback']);
});



