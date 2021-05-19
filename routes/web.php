<?php

use ppeCore\dvtinh\Http\Controllers\AuthController;

Route::group(['prefix' => 'ppe-core/auth'], function() {
    Route::post("/register", [AuthController::class, 'register']);
    Route::post("/login", [AuthController::class, 'login']);
});
