<?php

use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\GoalController;
use App\Services\GoogleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::resource('attachment', AttachmentController::class)
    ->middleware('auth');

Route::resource('momo', \App\Http\Controllers\MomoController::class);
//    ->middleware('auth');

Route::get('goals/gantt-chart/{user_id}', [GoalController::class, 'ganttChart']);