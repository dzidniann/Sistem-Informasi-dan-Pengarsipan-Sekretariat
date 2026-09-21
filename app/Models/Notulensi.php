<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Notulensi extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'judul_rapat',
        'tanggal_rapat',
        'notulis',
        'deskripsi_singkat',
        'foto_path',
        'file_path',
        'bidang_id',
        'created_by',
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

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}