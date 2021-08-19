<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\URL;

class GoogleService
{
    static $redirectUrl;
    static $platform = 'google';
    static $client_id;
    static $client_secret;

    static protected function __init()
    {
        $redirect_url = URL::to('/api/auth-service/handle');
        $redirect_url = str_replace('http:', 'https:', $redirect_url);
        self::$redirectUrl = $redirect_url;
        self::$client_id = getenv('GOOGLE_CLIENT_ID');
        self::$client_secret = getenv('GOOGLE_CLIENT_SECRET');
    }

    static public function generateUrl()
    {
        self::__init();
        $params = [
            'client_id'     => self::$client_id,
            'redirect_uri'  => self::$redirectUrl,
            'scope'         => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
            'response_type' => 'code',
            'access_type'   => 'offline',
            'prompt'        => '',
            'state'         => json_encode([
                'platform' => self::$platform,
            ])
        ];
        $params = http_build_query($params);
        return "https://accounts.google.com/o/oauth2/v2/auth?{$params}";
    }

    static public function handle($code)
    {
        self::__init();
        $client = new Client();
        $data = [
            'client_id'     => self::$client_id,
            'client_secret' => self::$client_secret,
            'redirect_uri'  => self::$redirectUrl,
            'grant_type'    => 'authorization_code',
            'code'          => $code,
        ];
        $res = $client->request('POST', "https://oauth2.googleapis.com/token", [
            'headers'     => [
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
        $user = [
            'name'         => $info['name'],
            'email'        => $info['email'],
            'platform'     => self::$platform,
            'access_token' => $accessToken['access_token'],
            'first_name'   => $info['family_name'],
            'last_name'    => $info['given_name'],
            'social_id'    => $info['id'],
            'avatar'       => $info['picture']
        ];
        return $user;
    }
}

