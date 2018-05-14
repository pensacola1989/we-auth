<?php

namespace App\Exceptions;

use App\Services\Exception\EntityNotFoundException;
use App\Services\Exception\VerifyCodeException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        $errResponse = [];

        if ($e instanceof EntityNotFoundException) {
            return response([
                'errCode' => 101,
                'msg' => 'Entity Not Found'
            ]);
        }
        if ($e instanceof ValidationException) {
            return response([
                'errCode' => 201,
                'msg' => $e->validator->errors()
            ]);
        }
        if ($e instanceof JWTException) {
            return response([
                'errCode' => 401,
                'msg' => 'token_absent'
            ]);
        }
        if ($e instanceof TokenInvalidException) {
            return response([
                'errCode' => 401,
                'msg' => 'token_invalid'
            ]);
        }
        if ($e instanceof VerifyCodeException) {
            return response([
                'errCode' => 401,
                'msg' => 'verify_invalid'
            ]);
        }
        if ($e instanceof TokenExpiredException) {
            return response([
                'errCode' => 401,
                'msg' => 'token_expired'
            ]);
        }

        return parent::render($request, $e);
    }
}
