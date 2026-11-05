<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NamedUploads
{
    public static function store(UploadedFile $file, string $directory, string $disk, int $id, string $field): string
    {
        $original = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $extension = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', pathinfo($original, PATHINFO_EXTENSION)));
        $extensions = [
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp',
            'bmp',
            'avif',
            'heic',
            'tif',
            'tiff',
        ];
        if (!in_array($extension, $extensions, true)) {
            $extension = $file->guessExtension();
        }
        if (!in_array($extension, $extensions, true)) {
            $extension = 'bin';
        }
        $stem = Str::ascii(pathinfo($original, PATHINFO_FILENAME));
        $stem = trim(preg_replace('/[^a-zA-Z0-9_-]+/', '_', $stem), '_-');
        $name = $id . '_' . substr($stem ?: 'dokumen', 0, 160) . ($extension ? '.' . $extension : '');
        $storage = Storage::disk($disk);
        $base = trim($directory, '/') . '/' . $id . '/' . $field;
        $storage->makeDirectory($base);

        // Reserve a revision directory atomically; filenames never need random suffixes.
        for ($revision = 1; ; $revision++) {
            $folder = $base . '/revision-' . $revision;
            if (@mkdir($storage->path($folder))) {
                break;
            }
            if (!is_dir($storage->path($folder))) {
                throw new \RuntimeException('Direktori unggahan tidak dapat dibuat.');
            }
        }

        try {
            $path = app(UploadOptimizer::class)->withPrepared(
                $file,
                fn (UploadedFile $prepared) => $storage->putFileAs($folder, $prepared, $name),
            );
            if (!$path) {
                throw new \RuntimeException('Berkas gagal disimpan.');
            }
        } catch (\Throwable $error) {
            $storage->delete($folder . '/' . $name);
            throw $error;
        }

        return $path;
    }

    /** Each file entry is [UploadedFile, directory, disk]. */
    public static function create(string $class, array $attributes, array $files): Model
    {
        $written = [];
        try {
            return DB::transaction(function () use ($class, $attributes, $files, &$written) {
                foreach ($files as $field => $upload) {
                    $attributes[$field] = '';
                }
                $record = $class::create($attributes);
                foreach ($files as $field => [$file, $directory, $disk]) {
                    $path = self::store($file, $directory, $disk, (int) $record->getKey(), $field);
                    $written[] = [$disk, $path];
                    $record->$field = $path;
                }
                if ($files) {
                    $record->saveQuietly();
                }

                return $record;
            });
        } catch (\Throwable $error) {
            foreach ($written as [$disk, $path]) {
                Storage::disk($disk)->delete($path);
            }
            throw $error;
        }
    }
}
