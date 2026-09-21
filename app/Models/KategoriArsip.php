<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class KategoriArsip extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'nama',
        'bidang_id',
        'parent_id',
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

    public function parent()
    {
        return $this->belongsTo(KategoriArsip::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(KategoriArsip::class, 'parent_id');
    }

    public function arsip()
    {
        return $this->hasMany(Arsip::class, 'kategori_id');
    }
}