<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class HumasPrivateFiles
{
    public static function path(?string $path): ?string
    {
        if (!$path || !preg_match('~^humas-private/[a-zA-Z0-9/_\-.]+$~D', $path) || str_contains($path, '..')) {
            return null;
        }
        $root = realpath(Storage::disk('local')->path('humas-private'));
        $real = realpath(Storage::disk('local')->path($path));

        return $root && $real && str_starts_with($real, $root . DIRECTORY_SEPARATOR) && is_file($real) ? $real : null;
    }
}
