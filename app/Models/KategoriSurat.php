<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class KategoriSurat extends Model
{
    use LogsActivity;

    protected $table = 'master_kategori_surat';
    protected $primaryKey = 'id_kategori';

    protected $fillable = [
        'kode_kategori',
        'nama_kategori',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Spatie Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'kode_kategori',
                'nama_kategori',
                'is_active',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getRouteKeyName(): string
{
    return 'id_kategori';
}
}