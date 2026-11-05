<?php

namespace Tests\Feature;

use App\Services\NamedUploads;
use App\Services\UploadOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class UploadOptimizationTest extends TestCase
{
    private array $temporary = [];

    protected function tearDown(): void
    {
        foreach ($this->temporary as $path) {
            @unlink($path);
        }
        parent::tearDown();
    }

    private function path(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'optimizer-test-');
        $this->temporary[] = $path;

        return $path;
    }

    public function test_png_is_reencoded_before_storage_without_changing_pixels(): void
    {
        Storage::fake('local');
        $source = $this->path();
        $image = imagecreatetruecolor(320, 240);
        imagefill($image, 0, 0, imagecolorallocate($image, 12, 80, 120));
        imagepng($image, $source, 0);
        imagedestroy($image);
        $originalHash = hash_file('sha256', $source);
        $stored = NamedUploads::store(
            new UploadedFile($source, 'Foto CSR.png', null, null, true),
            'photos',
            'local',
            42,
            'foto',
        );
        $result = Storage::disk('local')->path($stored);
        $this->assertSame('42_Foto_CSR.png', basename($stored));
        $this->assertLessThan(filesize($source), filesize($result));
        $decoded = imagecreatefrompng($result);
        $this->assertSame(320, imagesx($decoded));
        $this->assertSame(240, imagesy($decoded));
        $this->assertSame((12 << 16) | (80 << 8) | 120, imagecolorat($decoded, 10, 10));
        imagedestroy($decoded);
        $this->assertSame($originalHash, hash_file('sha256', $source));
    }

    public function test_jpeg_keeps_the_correct_orientation_after_removing_exif_metadata(): void
    {
        $source = $this->path();
        $image = imagecreatetruecolor(120, 80);
        for ($y = 0; $y < 80; $y++) {
            for ($x = 0; $x < 120; $x++) {
                imagesetpixel(
                    $image,
                    $x,
                    $y,
                    (($x * 37 + $y * 19) % 256 << 16) | (($x * 11 + $y * 43) % 256 << 8) | ($x * 71 + $y * 17) % 256,
                );
            }
        }
        imagejpeg($image, $source, 100);
        imagedestroy($image);
        $exif = "Exif\0\0II\x2a\0\x08\0\0\0\x01\0\x12\x01\x03\0\x01\0\0\0\x06\0\0\0\0\0\0\0";
        $jpeg = file_get_contents($source);
        file_put_contents(
            $source,
            substr($jpeg, 0, 2) . "\xff\xe1" . pack('n', strlen($exif) + 2) . $exif . substr($jpeg, 2),
        );
        $this->assertSame(6, exif_read_data($source)['Orientation']);
        (new UploadOptimizer())->withPrepared(new UploadedFile($source, 'foto.jpg', null, null, true), function (
            $prepared,
        ) use ($source) {
            $this->assertLessThan(filesize($source), filesize($prepared->getRealPath()));
            $size = getimagesize($prepared->getRealPath());
            $this->assertSame(80, $size[0]);
            $this->assertSame(120, $size[1]);
        });
    }

    private function office(bool $signed = false): string
    {
        $path = $this->path();
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::OVERWRITE);
        foreach (
            [
                '[Content_Types].xml' => '<Types/>',
                'word/document.xml' => '<document>' . str_repeat('Isi dokumen tetap sama. ', 5000) . '</document>',
            ]
            as $name => $content
        ) {
            $zip->addFromString($name, $content);
            $zip->setCompressionName($name, ZipArchive::CM_STORE);
        }
        if ($signed) {
            $zip->addFromString('_xmlsignatures/sig1.xml', '<Signature/>');
        }
        $zip->close();

        return $path;
    }

    public function test_docx_recompression_preserves_every_package_part_and_cleans_temporary_copy(): void
    {
        $source = $this->office();
        $preparedPath = null;
        $result = (new UploadOptimizer())->withPrepared(
            new UploadedFile($source, 'proposal.docx', null, null, true),
            function ($prepared) use (&$preparedPath, $source) {
                $preparedPath = $prepared->getRealPath();
                $this->assertNotSame($source, $preparedPath);
                $this->assertLessThan(filesize($source), filesize($preparedPath));
                $before = new ZipArchive();
                $after = new ZipArchive();
                $before->open($source);
                $after->open($preparedPath);
                $this->assertSame($before->numFiles, $after->numFiles);
                for ($i = 0; $i < $before->numFiles; $i++) {
                    $this->assertSame($before->getFromIndex($i), $after->getFromName($before->getNameIndex($i)));
                }
                $before->close();
                $after->close();

                return 'stored';
            },
        );
        $this->assertSame('stored', $result);
        $this->assertFileDoesNotExist($preparedPath);
    }

    public function test_signed_office_and_pdf_files_are_kept_byte_identical(): void
    {
        $pdf = $this->path();
        file_put_contents($pdf, "%PDF-1.4\n/ByteRange [0 10 20 30]\n%%EOF");
        foreach ([$this->office(true) => 'signed.docx', $pdf => 'signed.pdf'] as $source => $name) {
            $expected = hash_file('sha256', $source);
            (new UploadOptimizer())->withPrepared(new UploadedFile($source, $name, null, null, true), function (
                $prepared,
            ) use ($expected) {
                $this->assertSame($expected, hash_file('sha256', $prepared->getRealPath()));
            });
        }
    }

    private function pdf(): string
    {
        $stream = 'BT /F1 12 Tf 72 720 Td (' . str_repeat('Laporan kegiatan CSR ', 1500) . ') Tj ET';
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream",
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $i + 1 . " 0 obj\n" . $object . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF\n";
        $path = $this->path();
        file_put_contents($path, $pdf);

        return $path;
    }

    public function test_real_qpdf_optimizes_a_pdf_and_preserves_its_text(): void
    {
        $binary = base_path('tools/qpdf/bin/qpdf.exe');
        if (!is_file($binary)) {
            $binary = (new \Symfony\Component\Process\ExecutableFinder())->find('qpdf');
        }
        if (!$binary) {
            $this->markTestSkipped('Install qpdf to run PDF integration test.');
        }
        config(['uploads.qpdf_binary' => $binary, 'uploads.require_pdf_optimizer' => true]);
        $source = $this->pdf();
        (new UploadOptimizer())->withPrepared(new UploadedFile($source, 'report.pdf', null, null, true), function (
            $prepared,
        ) use ($source, $binary) {
            $this->assertLessThan(filesize($source), filesize($prepared->getRealPath()));
            $decoded = $this->path();
            $process = new \Symfony\Component\Process\Process([
                $binary,
                '--qdf',
                '--object-streams=disable',
                '--stream-data=uncompress',
                $prepared->getRealPath(),
                $decoded,
            ]);
            $process->mustRun();
            $this->assertStringContainsString(str_repeat('Laporan kegiatan CSR ', 1500), file_get_contents($decoded));
        });
    }

    public function test_missing_required_pdf_optimizer_rejects_upload(): void
    {
        config(['uploads.qpdf_binary' => 'qpdf-unavailable-for-test', 'uploads.require_pdf_optimizer' => true]);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        (new UploadOptimizer())->withPrepared(
            new UploadedFile($this->pdf(), 'report.pdf', null, null, true),
            fn() => null,
        );
    }
}
