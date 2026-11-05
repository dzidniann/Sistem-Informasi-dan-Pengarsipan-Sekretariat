<?php

namespace App\Console\Commands;

use App\Services\ProtectedUploads;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ProtectPublicUploads extends Command
{
    protected $signature = 'files:protect-public {--dry-run : List files without changing storage}';

    protected $description = 'Back up, verify, and move legacy public uploads to private storage without changing database paths';

    public function handle(): int
    {
        $source = Storage::disk('public');
        $target = Storage::disk('protected');
        $files = array_values(array_filter($source->allFiles(), fn ($path) => ! str_starts_with(basename($path), '.')));
        // Validate every path before moving any file.
        foreach ($files as $path) {
            if (! ProtectedUploads::path($path, 'public')) {
                $this->error('Unsafe or unsupported source path: '.$path);

                return self::FAILURE;
            }
        }
        if ($this->option('dry-run')) {
            $this->info(count($files).' public uploads would be protected.');

            return self::SUCCESS;
        }

        $backup = 'private-backups/protected-uploads-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(4));
        $manifest = [];
        foreach ($files as $path) {
            $original = ProtectedUploads::path($path, 'public');
            $hash = hash_file('sha256', $original);
            $existing = ProtectedUploads::path($path);
            if ($existing && hash_file('sha256', $existing) !== $hash) {
                $this->error('Private destination already contains a different file: '.$path);

                return self::FAILURE;
            }
            $stream = fopen($original, 'rb');
            try {
                if (! Storage::disk('local')->put($backup.'/'.$path, $stream)) {
                    throw new \RuntimeException('Upload backup failed.');
                }
            } finally {
                fclose($stream);
            }
            if (hash_file('sha256', Storage::disk('local')->path($backup.'/'.$path)) !== $hash) {
                throw new \RuntimeException('Upload backup checksum mismatch.');
            }
            if (! $existing) {
                $stream = fopen($original, 'rb');
                try {
                    $target->put($path, $stream);
                } finally {
                    fclose($stream);
                }
            }
            $protected = ProtectedUploads::path($path);
            if (! $protected || hash_file('sha256', $protected) !== $hash) {
                throw new \RuntimeException('Private upload checksum mismatch.');
            }
            if (! $source->delete($path)) {
                throw new \RuntimeException('Cannot remove the public copy after verification.');
            }
            $manifest[] = ['path' => $path, 'sha256' => $hash];
            Storage::disk('local')->put($backup.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        }
        $this->info(count($manifest).' files protected. Backup: storage/app/'.$backup);

        return self::SUCCESS;
    }
}
