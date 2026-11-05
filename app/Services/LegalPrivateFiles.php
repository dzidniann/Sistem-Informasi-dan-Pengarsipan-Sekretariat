<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class LegalPrivateFiles
{
    public static function path(?string $path): ?string
    {
        if (!$path || !preg_match('~^legal-private/[a-zA-Z0-9/_\-.]+$~D', $path) || str_contains($path, '..')) {
            return null;
        }
        $root = realpath(Storage::disk('local')->path('legal-private'));
        $real = realpath(Storage::disk('local')->path($path));

        return $root && $real && str_starts_with($real, $root . DIRECTORY_SEPARATOR) && is_file($real) ? $real : null;
    }

    public static function image(?string $path): ?string
    {
        $real = self::path($path);
        if (!$real || !in_array(mime_content_type($real), ['image/jpeg', 'image/png'], true)) {
            return null;
        }
        // DomPDF needs GD for PNG. The filename remains listed when thumbnails are unavailable.
        if (mime_content_type($real) === 'image/png' && !extension_loaded('gd')) {
            return null;
        }

        return 'data:' . mime_content_type($real) . ';base64,' . base64_encode(file_get_contents($real));
    }
}
