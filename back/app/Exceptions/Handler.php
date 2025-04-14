<?php

namespace App\Exceptions;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof ThrottleRequestsException) {
            Bugsnag::notifyException($e, function ($report) use ($request) {
                $report->setMetaData([
                    'request' => [
                        'ip' => $request->ip(),
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'body' => $request->all(),
                    ],
                ]);
            });
        }

        return parent::render($request, $e);
    }
}
