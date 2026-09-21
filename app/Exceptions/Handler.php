<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
      //  $this->reportable(function (Throwable $e) {
            //
$this->reportable(function (Throwable $e) {
        file_put_contents('/tmp/debug_error.log', 
            date('Y-m-d H:i:s') . ' - ' . get_class($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n", 
            FILE_APPEND
        );
    });
    }
}
