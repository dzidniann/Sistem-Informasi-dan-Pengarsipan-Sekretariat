<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Arsip extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'judul_file',
        'tanggal',
        'file_path',
        'kategori_id',
        'bidang_id',
        'uploaded_by',
    ];

    /**
     * Konfigurasi Activity Log Spatie
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriArsip::class, 'kategori_id');
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}