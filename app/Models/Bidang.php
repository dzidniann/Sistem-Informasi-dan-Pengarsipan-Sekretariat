<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Bidang extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'bidangs';

    protected $fillable = ['nama_bidang', 'akses_semua_bidang'];

    /**
     * Konfigurasi Activity Log Spatie
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function kategoriArsip()
    {
        return $this->hasMany(KategoriArsip::class);
    }
}
