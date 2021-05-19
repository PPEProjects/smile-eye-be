<?php

use Illuminate\Http\Response;
use ppeCore\dvtinh\Services\ResponseApi;

if (!function_exists('response_api')) {
    function response_api($data = '', $code = Response::HTTP_OK, $message = null)
    {
        $response = app(ResponseApi::class);
        return $response->send($data, $code, $message);
    }
}