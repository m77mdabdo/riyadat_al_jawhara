<?php

use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Support\MailFailureLog;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Exception\RfcComplianceException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Only requests for APP_URL's host (and its subdomains) are answered
        // outside local development and tests; any other Host header gets a
        // 400 Bad Request, so it can never end up in a generated link.
        $middleware->trustHosts();

        $middleware->web(append: [
            SetLocale::class,
            SecurityHeaders::class,
        ]);

        // The language must be known before route-model binding runs, so a
        // 404 for an unknown slug renders in the visitor's language.
        $middleware->prependToPriorityList(SubstituteBindings::class, SetLocale::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // A failed email (sent now, or by the queue worker) is logged without
        // the recipient's address, which mail servers echo in their errors.
        $exceptions->report(function (TransportExceptionInterface|RfcComplianceException $e) {
            MailFailureLog::write('Email could not be sent', $e);
        })->stop();
    })->create();
