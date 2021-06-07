<?php

namespace ppeCore\dvtinh\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
//use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Http\Request;

use ppeCore\dvtinh\Models\PasswordReset;
use ppeCore\dvtinh\Notifications\ResetPasswordRequest;
use Exception;

class ResetPasswordController extends Controller
{
    /**
     * Create token password reset.
     *
     * @param  ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function sendMail(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if(!$user){
            throw new Exception(__('ppe.email_not_exist'));
        };
        $user->get();
        $passwordReset = PasswordReset::updateOrCreate([
            'email' => $user->email,
        ], [
            'token' => Str::random(60),
        ]);
        if ($passwordReset) {
            $user->notify(new ResetPasswordRequest($passwordReset->token));
        }

        return response()->json([
        'status' => true,
        ]);
    }

    public function reset(Request $request)
    {
        $passwordReset = PasswordReset::where('token', $request['token'])->first();
        if(!$passwordReset){
            throw new Exception(__('ppe.token_invalid'));
        }
        $passwordReset->get();
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();

            throw new Exception(__('ppe.token_invalid'));
        }
        $user = User::where('email', $passwordReset->email)->firstOrFail();
        $updatePasswordUser = $user->password = Hash::make($request['password']);
        $user->save();
        $passwordReset->delete();

        return response()->json([
            'status' => true,
        ]);
    }
}