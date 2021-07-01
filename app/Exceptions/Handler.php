<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Throwable $exception
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        $handle = parent::render($request, $exception);
        $message = $exception->getMessage();
        $code = $handle->getStatusCode();
        if(config('app.debug') === false) {
            if ($exception instanceof \PDOException) {
                $message = '[' . $code . '] There is unexpected issue occurred. Please try again or contact our support.';
                $code = 422;
                return response_api($message, $code);
            }

            if ($exception instanceof ValidationException) {
                return $this->handleValidationException($exception);
            }

            if ($request->expectsJson() || Request::is('api/*')) {
                return response_api($message, $code);
            }
            if ($exception instanceof \PDOException) {
                $message = '[' . $code . '] There is unexpected issue occurred. Please try again or contact our support.';
                $code = 422;
                return response_api($message, $code);
            }

            if ($exception instanceof ValidationException) {
                return $this->handleValidationException($exception);
            }
            if ($request->expectsJson() || Request::is('api/*')) {
                return response_api($message, $code);
            }
        }
        return parent::render($request, $exception);
    }

    protected function handleValidationException(ValidationException $exception)
    {
        $errors = $exception->errors();
        $defaultMessage = Arr::first($errors);
        return response()->json([
            'status'  => false,
            'message' => $defaultMessage[0],
            'errors'  => $errors,
        ], 400);
    }
}
