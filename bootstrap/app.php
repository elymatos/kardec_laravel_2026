<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\AuthenticateAdmin;
use App\Http\Middleware\AuthenticateMaster;
use App\Http\Middleware\Data;
use App\Http\Middleware\Session;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //        $middleware->append(Data::class);
        $middleware->group('web', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            ValidateCsrfToken::class,
            SubstituteBindings::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            Session::class,
        ]);
        $middleware->group('auth', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            ValidateCsrfToken::class,
            SubstituteBindings::class,
            Session::class,
            Authenticate::class,
        ]);
        $middleware->appendToGroup('master', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            ValidateCsrfToken::class,
            SubstituteBindings::class,
            Session::class,
            AuthenticateMaster::class,
        ]);
        $middleware->appendToGroup('admin', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            ValidateCsrfToken::class,
            SubstituteBindings::class,
            Session::class,
            AuthenticateAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
        //        $exceptions->renderable(function (\Illuminate\Validation\ValidationException $e) {
        //
        //            debug($e->getMessage());
        //            $request = request();
        //            if ($request->header('HX-Request')) {
        //                debug('is htmx', $e->redirectTo);
        //                return response('')
        //                    ->withHeaders([
        //                        'HX-Redirect' => $e->redirectTo
        //                    ]);
        //            }
        //        });
    })->create();
