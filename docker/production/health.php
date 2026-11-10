<?php

// Do not expose exception details or credentials in Docker health output.
try {
    require '/var/www/html/vendor/autoload.php';
    $app = require '/var/www/html/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $app->make('db')->connection()->select('SELECT 1');
    if (! is_writable(storage_path('app/protected-uploads')) || ! @fsockopen('127.0.0.1', 9000)) {
        exit(1);
    }
    exit(0);
} catch (Throwable $exception) {
    exit(1);
}
