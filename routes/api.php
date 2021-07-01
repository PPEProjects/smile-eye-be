<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\GoalController;

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

Route::get('/test', function (Request $request) {
       dd("hello");
});

Route::resource('attachment', AttachmentController::class)
    ->middleware('auth');

Route::get('goals/gantt-chart/{user_id}', [GoalController::class, 'ganttChart']);