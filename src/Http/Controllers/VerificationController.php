<?php

namespace ppeCore\dvtinh\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class VerificationController extends Controller {
    public function verify($user_id, Request $request) {
//        if (!$request->hasValidSignature()) {
//            return response()->json(["msg" => "Invalid/Expired url provided."], 401);
//        }

        $user = User::findOrFail($user_id);
        $user->sendEmailVerificationNotification();
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return "check email";
    }

    public function resend() {
        if (auth()->user()->hasVerifiedEmail()) {
            return response()->json(["msg" => "Email already verified."], 400);
        }

        auth()->user()->sendEmailVerificationNotification();

        return response()->json(["msg" => "Email verification link sent on your email id"]);
    }
}