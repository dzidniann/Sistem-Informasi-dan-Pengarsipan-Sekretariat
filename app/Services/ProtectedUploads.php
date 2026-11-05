<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ProtectedUploads
{
    public static function path(string $path, string $disk = 'protected'): ?string
    {
        if (! self::validPath($path)) {
            return null;
        }
        $root = realpath(Storage::disk($disk)->path(''));
        $file = realpath(Storage::disk($disk)->path($path));

        return $root && $file && is_file($file) && str_starts_with($file, rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR)
            ? $file : null;
    }

    public static function validPath(string $path): bool
    {
        if (! $path || str_starts_with($path, '/') || ! preg_match('~^[\pL\pN ._()\[\]/-]+$~uD', $path)) {
            return false;
        }
        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || str_starts_with($segment, '.')) {
                return false;
            }
        }

        return true;
    }

    public static function response(string $path, bool $download = false)
    {
        // Legacy storage is a temporary fallback while the migration command runs.
        $file = self::path($path) ?? self::path($path, 'public');
        abort_unless($file, 404);
        $mime = mime_content_type($file) ?: 'application/octet-stream';
        $inline = ! $download && in_array($mime, ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'], true);

        return response()->download($file, basename($path), [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; frame-ancestors 'self'",
        ], $inline ? 'inline' : 'attachment');
    }
}
