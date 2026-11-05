<?php

namespace App\Services;

use App\Helpers\RomawiHelper;
use App\Models\ArsipSurat;
use App\Models\KategoriSurat;
use Illuminate\Support\Facades\DB;

class NomorSuratService
{
    public function nomorSelanjutnya(int $idKategori, int $tahun): int
    {
        $max = ArsipSurat::where('id_kategori', $idKategori)
            ->where('tahun', $tahun)
            ->where('is_backdate', false)
            ->max('nomor_urut');

        return ($max ?? 0) + 1;
    }

    public function rilis(
        int $idKategori,
        int $nomorUrut,
        ?int $userId,
        int $bulan,
        int $tahun,
        array $detail = [],
        array $files = [],
    ): ArsipSurat {
        $kategori = KategoriSurat::findOrFail($idKategori);
        $bulanRomawi = RomawiHelper::dariBulan($bulan);

        return DB::transaction(function () use ($kategori, $nomorUrut, $tahun, $bulanRomawi, $userId, $detail, $files) {
            $sudahAda = ArsipSurat::where('id_kategori', $kategori->id_kategori)
                ->where('tahun', $tahun)
                ->where('nomor_urut', $nomorUrut)
                ->lockForUpdate()
                ->exists();

            $nomorFormat = sprintf('%03d/%s/%s/%d', $nomorUrut, $kategori->kode_kategori, $bulanRomawi, $tahun);

            if ($sudahAda) {
                $nomorFormat .= '/THB';
            }

            return NamedUploads::create(
                ArsipSurat::class,
                array_merge($detail, [
                    'id_kategori' => $kategori->id_kategori,
                    'nomor_urut' => $nomorUrut,
                    'bulan_romawi' => $bulanRomawi,
                    'tahun' => $tahun,
                    'is_backdate' => $sudahAda,
                    'full_nomor' => $nomorFormat,
                    'dirilis_oleh' => $userId,
                ]),
                $files,
            );
        });
    }
}
