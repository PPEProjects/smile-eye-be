<?php

namespace ppeCore\dvtinh\Http\Controllers;

use Illuminate\Support\Facades\DB;
use ppeCore\dvtinh\Http\Requests\AuthRequest;
use ppeCore\dvtinh\Models\User;

class AuthController extends \App\Http\Controllers\Controller
{
    public function register(AuthRequest $request)
    {
        try {
            DB::beginTransaction();
            $req = $request->all();
            $user = User::create($req);
            DB::commit();
            $access_token = $user->createToken('authToken')->accessToken;

            return response(['user' => $user, 'access_token' => $access_token]);
            throw new Exception(__('smile_eyes.something_wrong'));
        } catch (\PDOException $exception) {
            DB::rollBack();
            throw new PDOException($exception->getMessage());
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage());
        }
    }
}
