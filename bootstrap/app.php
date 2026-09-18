<?php

use App\Base\Exceptions\CustomValidationException;
use App\Http\Middleware\AgentAddonChecking;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RedirectDynamicLoginUrl;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RemoveEmptyQueryParams;
use App\Http\Middleware\RouteNeedsPermission;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\ValidateSignature;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )->withMiddleware(function (Middleware $middleware) {

        /*
        |--------------------------------------------------------------------------
        | Global Middleware ( $middleware )
        |--------------------------------------------------------------------------
        */
        $middleware->append([
            CheckForMaintenanceMode::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Middleware Groups ( $middlewareGroups )
        |--------------------------------------------------------------------------
        */
        $middleware->group('web', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            HandleInertiaRequests::class,
        ]);

        $middleware->group('api', [
            ThrottleRequests::class.':60,1',
            SubstituteBindings::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Route Middleware Aliases ( $routeMiddleware )
        |--------------------------------------------------------------------------
        */
        $middleware->alias([
            'auth' => Authenticate::class,
            'auth.basic' => AuthenticateWithBasicAuth::class,
            'bindings' => SubstituteBindings::class,
            'can' => Authorize::class,
            'signed' => ValidateSignature::class,
            'guest' => RedirectIfAuthenticated::class,
            'throttle' => ThrottleRequests::class,
            'remove_empty_query' => RemoveEmptyQueryParams::class,
            'permission' => RouteNeedsPermission::class,
            'redirect_dynamic_login' => RedirectDynamicLoginUrl::class,
            'agent_addons' => AgentAddonChecking::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // App\Exceptions\Handler is not registered under Laravel 12, so the app's own
        // validation exception must be rendered here (it otherwise surfaces as a 500).
        $exceptions->dontReport([CustomValidationException::class]);

        $exceptions->render(function (CustomValidationException $e, $request) {
            $errors = $e->getMessages();

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => collect($errors)->flatten()->first() ?: $e->getMessage(),
                    'status_code' => 422,
                    'errors' => $errors,
                ], 422);
            }

            return back()->withErrors(collect($errors)->map(fn ($m) => is_array($m) ? $m[0] : $m)->all())->withInput();
        });

        $exceptions->render(function (Throwable $e, $request) {
            // if ($e instanceof \App\Exceptions\CustomException) {
            //     return response()->json([
            //         'message' => $e->getMessage(),
            //     ], 400);
            // }
        });
    })->create();
