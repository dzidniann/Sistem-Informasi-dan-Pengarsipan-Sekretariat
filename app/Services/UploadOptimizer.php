<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use ZipArchive;

class UploadOptimizer
{
    /** Process a private temporary copy, then hand it to storage. Always clean up. */
    public function withPrepared(UploadedFile $file, callable $store): mixed
    {
        $source = $file->getRealPath();
        $temporary = tempnam(sys_get_temp_dir(), 'upload-opt-');
        if ($temporary === false) {
            throw new \RuntimeException('Berkas sementara tidak dapat dibuat.');
        }

        try {
            $mime = $file->getMimeType();
            $success = match (true) {
                $mime === 'application/pdf' => $this->pdf($source, $temporary),
                in_array($mime, ['image/jpeg', 'image/png'], true) => $this->image($source, $temporary, $mime),
                in_array(strtolower($file->getClientOriginalExtension()), ['docx', 'xlsx'], true) => $this->office(
                    $source,
                    $temporary,
                    strtolower($file->getClientOriginalExtension()),
                ),
                default => false,
            };

            clearstatcache(true, $temporary);
            if ($success && filesize($temporary) > 0 && filesize($temporary) < filesize($source)) {
                return $store(new UploadedFile($temporary, $file->getClientOriginalName(), $mime, null, true));
            }

            return $store($file);
        } finally {
            @unlink($temporary);
        }
    }

    private function image(string $source, string $output, string $mime): bool
    {
        $size = @getimagesize($source);
        // Bound decoded memory, including a second buffer for EXIF rotation.
        if (!$size || $size[0] * $size[1] > 12_000_000 || !extension_loaded('gd')) {
            return false;
        }
        // Without EXIF support, re-encoding could silently rotate phone photos.
        if ($mime === 'image/jpeg' && !function_exists('exif_read_data')) {
            return false;
        }

        $image = $mime === 'image/png' ? @imagecreatefrompng($source) : @imagecreatefromjpeg($source);
        if (!$image) {
            return false;
        }

        try {
            if ($mime === 'image/png') {
                imagealphablending($image, false);
                imagesavealpha($image, true);

                return imagepng($image, $output, 9);
            }

            $exif = function_exists('exif_read_data') ? @exif_read_data($source) : false;
            $orientation = (int) ($exif['Orientation'] ?? 1);
            $angle = match ($orientation) {
                3 => 180,
                5, 6 => -90,
                7, 8 => 90,
                default => 0,
            };
            if ($angle) {
                $rotated = imagerotate($image, $angle, 0);
                if (!$rotated) {
                    return false;
                }
                imagedestroy($image);
                $image = $rotated;
            }
            if (in_array($orientation, [2, 4, 5, 7], true)) {
                imageflip($image, $orientation === 4 ? IMG_FLIP_VERTICAL : IMG_FLIP_HORIZONTAL);
            }
            imageinterlace($image, true);

            return imagejpeg($image, $output, 90);
        } finally {
            imagedestroy($image);
        }
    }

    private function pdf(string $source, string $output): bool
    {
        $bytes = file_get_contents($source);
        if (preg_match('~/ByteRange\b|/FT\s*/Sig\b|/Type\s*/Sig\b|/Encrypt\b~', $bytes)) {
            return false;
        }

        $configured = config('uploads.qpdf_binary');
        $binary = is_file($configured) ? realpath($configured) : (new ExecutableFinder())->find($configured);
        if (!$binary) {
            if (config('uploads.require_pdf_optimizer')) {
                throw ValidationException::withMessages([
                    'file' => 'Pengolahan PDF belum tersedia di server. Hubungi administrator.',
                ]);
            }

            return false;
        }

        // Inspect decoded form fields too, since field dictionaries may use object streams.
        $inspect = new Process([$binary, '--json', '--json-key=acroform', $source]);
        $inspect->setTimeout(30);
        $inspect->run();
        if (!$inspect->isSuccessful()) {
            return false;
        }
        $form = json_decode($inspect->getOutput(), true);
        if (!is_array($form)) {
            return false;
        }
        $signed = false;
        array_walk_recursive($form, function ($value) use (&$signed) {
            if ($value === '/Sig') {
                $signed = true;
            }
        });
        if ($signed) {
            return false;
        }

        $process = new Process([
            $binary,
            '--object-streams=generate',
            '--compress-streams=y',
            '--recompress-flate',
            '--compression-level=9',
            $source,
            $output,
        ]);
        $process->setTimeout(30);
        $process->run();
        if (!$process->isSuccessful()) {
            return false;
        }
        $check = new Process([$binary, '--check', $output]);
        $check->setTimeout(30);
        $check->run();

        return $check->isSuccessful();
    }

    private function office(string $source, string $output, string $extension): bool
    {
        $input = new ZipArchive();
        if ($input->open($source, ZipArchive::CHECKCONS) !== true) {
            return false;
        }
        $result = new ZipArchive();

        try {
            $required = $extension === 'docx' ? 'word/document.xml' : 'xl/workbook.xml';
            if (
                $input->locateName('[Content_Types].xml') === false ||
                $input->locateName($required) === false ||
                $input->numFiles > 5000
            ) {
                return false;
            }
            $total = 0;
            for ($i = 0; $i < $input->numFiles; $i++) {
                $entry = $input->statIndex($i);
                $total += $entry['size'];
                if (
                    str_starts_with(strtolower($entry['name']), '_xmlsignatures/') ||
                    ($entry['encryption_method'] ?? 0) !== 0 ||
                    $entry['size'] > 16 * 1024 * 1024 ||
                    $total > 100 * 1024 * 1024
                ) {
                    return false;
                }
            }
            if ($result->open($output, ZipArchive::OVERWRITE) !== true) {
                return false;
            }
            $result->setArchiveComment($input->getArchiveComment() ?: '');
            for ($i = 0; $i < $input->numFiles; $i++) {
                $entry = $input->statIndex($i);
                $content = $input->getFromIndex($i);
                if (
                    $content === false ||
                    !$result->addFromString($entry['name'], $content) ||
                    !$result->setCompressionName($entry['name'], ZipArchive::CM_DEFLATE, 9)
                ) {
                    return false;
                }
                $result->setMtimeName($entry['name'], $entry['mtime']);
            }

            return $result->close();
        } finally {
            $input->close();
            try {
                $result->close();
            } catch (\Throwable $ignored) {
                /* Already closed, or not opened. */
            }
        }
    }
}
