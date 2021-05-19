<?php

namespace ppeCore\dvtinh\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PDOException;
use ppeCore\dvtinh\Http\Requests\AuthRequest;
use ppeCore\dvtinh\Models\User;

class AuthController extends Controller
{
    public function register(AuthRequest $request)
    {
        try {
            DB::beginTransaction();
            $req = $request->all();

            $req['password'] = Hash::make($req['password']);
            $user = User::create($req);
            DB::commit();
            $access_token = $user->createToken('authToken')->accessToken;

            return response_api(['user' => $user, 'access_token' => $access_token]);
            throw new Exception(__('ppe.something_wrong'));
        } catch (\PDOException $exception) {
            DB::rollBack();
            throw new PDOException($exception->getMessage());
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage());
        }
    }

    public function login(AuthRequest $request)
    {
        try {
            DB::beginTransaction();
            $req = $request->all();
            if (!auth()->attempt($req)) {
                throw new Exception(__('ppe.invalid_credentials'));
            }
            $access_token = auth()->user()->createToken('authToken')->accessToken;
            $user = auth()->user()->toArray();
            return response_api(['user' => $user, 'access_token' => $access_token]);
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage());
        }
    }
}
