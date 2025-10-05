<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use App\Enums\ApiErrorCode;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\SentryUserContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ResponseBuilder::asError(401)
                    ->withMessage($e->getMessage())
                    ->build();
            }
        });

        $exceptions->renderable(function (AccessDeniedHttpException $e, $request) {
            $previous = $e->getPrevious();

            if (! ($previous instanceof AuthorizationException)) {
                return null;
            }

            return ResponseBuilder::asError($e->getStatusCode())
                ->withHttpCode($e->getStatusCode())
                ->withMessage($previous->getMessage())
                ->build();
        });

        $exceptions->renderable(function (NotFoundHttpException $e) {
            return ResponseBuilder::asError($e->getStatusCode())
                ->withHttpCode($e->getStatusCode())
                ->withMessage($e->getMessage())
                ->build();
        });

        $exceptions->renderable(function (AuthorizationException $e) {
            return ResponseBuilder::asError(ApiErrorCode::SOMETHING_WENT_WRONG->value)
                ->withHttpCode(400)
                ->withMessage($e->getMessage())
                ->build();
        });

        $exceptions->renderable(function (AuthenticationException $e) {
            return ResponseBuilder::asError(ApiErrorCode::SOMETHING_WENT_WRONG->value)
                ->withHttpCode(401)
                ->withMessage($e->getMessage())
                ->build();
        });

        $exceptions->renderable(function (ValidationException $exception) {
            return ResponseBuilder::asError(ApiErrorCode::SOMETHING_WENT_WRONG->value)
                ->withHttpCode(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->withMessage($exception->validator->getMessageBag()->first())
                ->withData($exception->validator->getMessageBag()->getMessages())
                ->build();
        });

        $exceptions->renderable(function (HttpException $e) {
            return ResponseBuilder::asError($e->getStatusCode())
                ->withHttpCode($e->getStatusCode())
                ->withMessage($e->getMessage())
                ->build();
        });
    })->create();
