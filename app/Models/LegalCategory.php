<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class LegalCategory extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'legal_categories';

    protected $fillable = ['name', 'description', 'kategori_arsip_id'];

    /**
     * Konfigurasi Activity Log Spatie
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function documents()
    {
        return $this->hasMany(LegalDocument::class, 'category_id');
    }

    public function master()
    {
        return $this->belongsTo(KategoriArsip::class, 'kategori_arsip_id');
    }

    public function scopeMasterHukum($query)
    {
        return $query->whereHas(
            'master.bidang',
            fn ($bidang) => $bidang->whereIn('nama_bidang', ['Sub Bidang Hukum', 'Divisi Hukum']),
        );
    }
}
