<?php

namespace Ronghz\LaravelDdd\Framework\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Ronghz\LaravelDdd\Helpers\Tools;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array<string>
     */
    protected $dontReport = [
        ValidationException::class,
        AuthorizationException::class,
    ];

    public function report(Exception $exception): void
    {
        parent::report($exception);
    }


    public function handle($request, Exception $exception): \Illuminate\Http\JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return response()->json(Tools::error($exception->getMessage()));
        } elseif ($exception instanceof AuthorizationException) {
            return response()->json(Tools::error($exception->getMessage()));
        } elseif ($exception instanceof DevelopException) {
            return response()->json(Tools::error($exception->getMessage(), $exception->getCode()));
        } elseif ($exception instanceof CommonException) {
            return response()->json(Tools::error($exception->getMessage(), $exception->getCode()));
        } elseif ($exception instanceof DomainException) {
            return response()->json(Tools::error($exception->getMessage(), $exception->getCode()));
        } elseif (env('APP_ENV') != 'master') {
            return response()->json(Tools::error($exception->getMessage(), $exception->getCode()));
        } else {
            return response()->json(Tools::error('系统开小差了'));
        }
    }
}
