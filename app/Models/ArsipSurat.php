<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ArsipSurat extends Model
{
    use LogsActivity;

    protected $table = 'arsip_surat';

    protected $primaryKey = 'id_surat';

    protected $fillable = [
        'id_kategori',
        'nomor_urut',
        'bulan_romawi',
        'tahun',
        'is_backdate',
        'full_nomor',
        'dirilis_oleh',
        'tanggal_surat',
        'perihal',
        'tujuan',
        'catatan',
        'file_path',
    ];

    protected $casts = [
        'is_backdate' => 'boolean',
        'tanggal_surat' => 'date',
    ];

    /**
     * Spatie Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id_kategori',
                'nomor_urut',
                'bulan_romawi',
                'tahun',
                'is_backdate',
                'full_nomor',
                'dirilis_oleh',
                'tanggal_surat',
                'perihal',
                'tujuan',
                'catatan',
                'file_path',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Route Model Binding
     */
    public function getRouteKeyName(): string
    {
        return 'id_surat';
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriSurat::class, 'id_kategori', 'id_kategori');
    }

    public function perilis()
    {
        return $this->belongsTo(User::class, 'dirilis_oleh');
    }
}
