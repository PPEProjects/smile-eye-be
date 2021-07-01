<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {

        $this->validateLogin($request);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if($this->guard()->validate($this->credentials($request))) {
            $key = 'email';
            if(is_numeric($request->get('email'))){
                $key = 'mobile_no';
            }
            if(\Auth::attempt([$key => $request->email, 'password' => $request->password, 'is_otp_verify' => 1])) {
                return redirect()->route('dashboard.index');
            }  else {
                $this->incrementLoginAttempts($request);
                \Session::flash('message', 'This account is not activated!');
                \Session::flash('alert-class', 'alert-danger');
                return redirect()->back();

            }
        } else {

            $this->incrementLoginAttempts($request);

            \Session::flash('message', 'Credentials do not match our database!');
            \Session::flash('alert-class', 'alert-danger');
            return redirect()->back();

        }
    }

    protected function credentials(Request $request)
    {
        if(is_numeric($request->get('email'))){
            return ['mobile_no'=>$request->get('email'),'password'=>$request->get('password')];
        }
        return $request->only($this->username(), 'password');
    }
}