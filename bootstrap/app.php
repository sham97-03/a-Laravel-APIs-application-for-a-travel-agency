<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        //api: __DIR__.'/../routes/api.php',
        //apiPrefix: 'v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        /*$exceptions->report(function (Throwable $e) {
            // Define how to report different types of exceptions
        });

        $exceptions->render(function (Throwable $e, $request) {
            if ($e instanceof ValidationException) {
                $errors = $e->validator->errors();
                $message = 'Validation failed for: ';
                foreach ($errors->getMessages() as $field => $messages) {
                    $message .= $field . ' - ' . implode(', ', $messages) . '; ';
                }

                return response()->json([
                    'message' => rtrim($message, '; '),
                    'errors' => $errors,
                ], 422);
            }
        });
    })*/
        $exceptions->render(function (Throwable $e, $request) {
            if ($e instanceof ValidationException) {
                $errors = $e->validator->errors();
                $message = 'Validation failed for: ';
                foreach ($errors->getMessages() as $field => $messages) {
                    $message .= $field.' - '.implode(', ', $messages).'; ';
                }

                return response()->json([
                    'message' => rtrim($message, '; '),
                    'errors' => $errors,
                ], 422);
            }
        });
    })
    ->create();
