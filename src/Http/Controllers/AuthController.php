<?php

namespace ppeCore\dvtinh\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PDOException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use ppeCore\dvtinh\Http\Requests\AuthRequest;
use ppeCore\dvtinh\Models\User;

class AuthController extends Controller
{
    private $redirect_url;
    function __construct(){
        $redirect_url = URL::to('ppe-core/auth/handle');
        $redirect_url = str_replace('http:','https:',$redirect_url);
        $this->redirect_url = $redirect_url;
    }
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

    public function generateUrl(Request $request)
    {
        if ($request->platform == 'google') {
            $params = http_build_query([
                'client_id' => config('services.google.client_id'),
                'redirect_uri' => $this->redirect_url,
                'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
                'response_type' => 'code',
                'access_type' => 'offline',
                'prompt' => '',
                'state' => json_encode([
                    'platform' => $request->platform,

                ])
            ]);
            return response()->json([
                'status' => true,
                'data' => "https://accounts.google.com/o/oauth2/v2/auth?{$params}"
            ]);
        }
        //---------------------------FACEBOOK-----------------------------
        if ($request->platform=='facebook'){
            $params = http_build_query([
                'client_id' => config('services.facebook.client_id') ,
                'redirect_uri' => $this->redirect_url,
                'scope'=>'email',
                'response_type'=>'code',
                'auth_type' => 'rerequest',
                'display' =>'popup',
                'state' =>json_encode([
                    'platform'=>$request->platform,
                ])
            ]);
            return response()->json([
                'status'=>true,
                'data'=>"https://www.facebook.com/v9.0/dialog/oauth?{$params}"
            ]);
        }
        return response()->json([
            'status' =>   false,
            'message'=> 'something was wrong !',
        ]);
    }


    function authHandle(Request $request)
    {
        $state = json_decode($request->state, true);
        $client = new Client();
        if ($state['platform'] == 'google') {
            $data = [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => $this->redirect_url,
                'grant_type' => 'authorization_code',
                'code' => $request->code,
            ];
            $res = $client->request('POST', "https://oauth2.googleapis.com/token", [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => $data
            ]);
            $accessToken = json_decode($res->getBody()->getContents(), true);
            $res = $client->request('GET', "https://www.googleapis.com/oauth2/v2/userinfo",
                [
                    'headers' => [
                        'Authorization' => "Bearer {$accessToken['access_token']}",
                    ],
                ]);
            $info = json_decode($res->getBody()->getContents(), true);
            $newUser = [
                'name' => $info['name'],
                'email' => $info['email'],
                'platform' => 'google',
                'access_token_social' => $accessToken['access_token'],
                'first_name' => $info['family_name'],
                'last_name' => $info['given_name'],
                'social_id' => $info['id'],
                'avatar' => $info['picture']
            ];

            $userCreate = User::updateOrCreate([
                'platform' => $newUser['platform'],
                'social_id' => $newUser['social_id']
            ],
                $newUser);
            $userCreate->token = $userCreate->createToken('authToken')->accessToken;
            return response()->json([
                'status' => true,
                'data' => $userCreate
            ]);

        }
        //-------------------------------FACEBOOK------------------------------
        if ($state['platform'] == 'facebook'){
            $res = $client->request('GET',"https://graph.facebook.com/v9.0/oauth/access_token",[
                'query'=>[
                    'client_id' => config('services.facebook.client_id') ,
                    'client_secret' => config('services.facebook.client_secret') ,
                    'redirect_uri' => $this->redirect_url,
                    'code'=>$request->code,
                ]
            ]);
            $accessToken = json_decode($res->getBody()->getContents(),true);
            $res = $client->request('GET',"https://graph.facebook.com/v9.0/me",
                [
                    'headers'=>[
                        'Authorization'=>"Bearer {$accessToken['access_token']}",
                    ],
                    'query'=>[
                        'fields'=>'id,email,first_name,last_name,picture'
                    ]
                ]);
            $info = json_decode($res->getBody()->getContents(),true);
            $newUser = [
                'email' => $info['email'],
                'platform' => 'facebook',
                'access_token_social' => $accessToken['access_token'],
                'first_name' => $info['first_name'],
                'name' => $info['last_name'],
                'social_id' =>$info['id'],
                'avatar' => $info['picture']['data']['url']
            ];

//            $userCreate = User::create($newUser);
            $userCreate = User::updateOrCreate([
                'platform'=>$newUser['platform'],
                'social_id' => $newUser['social_id']
            ],
                $newUser);
            $userCreate->token = $userCreate->createToken('authToken')->accessToken;
            return response()->json([
                'status'=>true,
                'data'=>$userCreate
            ]);
        }
    }
}
