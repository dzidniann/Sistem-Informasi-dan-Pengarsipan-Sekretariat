<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class HumasCsr extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    protected $casts = ['tanggal_proses' => 'date', 'nominal' => 'decimal:2'];

    public const STATUSES = [
        'DIBANTU' => 'DIBANTU',
        'BELUM_DIBANTU' => 'BELUM DIBANTU',
        'DIBUATKAN' => 'DIBUATKAN',
        'DIKETAHUI' => 'DIKETAHUI',
        'DILAKSANAKAN' => 'DILAKSANAKAN',
        'DIGANTI' => 'DIGANTI',
        'DIPENDING' => 'DIPENDING',
        'TIDAK_ADA' => '-',
    ];

    public const LUARAN = [
        'DANA' => 'DANA',
        'AIR_MINERAL' => 'AIR MINERAL',
        'BARANG' => 'BARANG',
        'TIDAK_ADA' => '-',
    ];

    public const CATEGORIES = [
        'CSR/TJSLP', 'SPONSORSHIP', 'UNDANGAN', 'INFORMASI', 'IKLAN',
        'PENGADAAN', 'SARANA/PRASARANA', 'BIASA', 'SURAT', 'NONE',
    ];

    public const STATUS_COLORS = [
        'DIBANTU' => ['#11734b', '#ffffff'],
        'BELUM_DIBANTU' => ['#cc0000', '#ffffff'],
        'DIBUATKAN' => ['#783f04', '#ffffff'],
        'DIKETAHUI' => ['#1155cc', '#ffffff'],
        'DILAKSANAKAN' => ['#674ea7', '#ffffff'],
        'DIGANTI' => ['#d9ead3', '#11734b'],
        'DIPENDING' => ['#f4cccc', '#cc0000'],
        'TIDAK_ADA' => ['#e8e8e8', '#444444'],
    ];

    public const LUARAN_COLORS = [
        'DANA' => ['#3c78d8', '#ffffff'],
        'AIR_MINERAL' => ['#674ea7', '#ffffff'],
        'BARANG' => ['#7f6000', '#ffffff'],
        'TIDAK_ADA' => ['#e8e8e8', '#444444'],
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? str_replace('_', ' ', $this->status);
    }

    public function getLuaranLabelAttribute(): string
    {
        return self::LUARAN[$this->luaran] ?? str_replace('_', ' ', $this->luaran);
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriArsip::class, 'kategori_id');
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments()
    {
        return $this->hasMany(HumasCsrAttachment::class);
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query->where('bidang_id', $user->bidang_id);
    }

    public function scopeFiltered($query, array $filters)
    {
        if (! empty($filters['cari'])) {
            $term = '%'.$filters['cari'].'%';
            $query->where(
                fn ($q) => $q
                    ->where('pemohon', 'like', $term)
                    ->orWhere('nama_kegiatan', 'like', $term)
                    ->orWhere('nomor_surat', 'like', $term)
                    ->orWhere('nomor_voucher', 'like', $term)
                    ->orWhere('keluar', 'like', $term)
                    ->orWhere('keterangan', 'like', $term)
                    ->orWhereHas('kategori', fn ($category) => $category->where('nama', 'like', $term)),
            );
        }
        foreach (['status', 'kategori_id', 'luaran'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }
        if (! empty($filters['tahun'])) {
            $query->whereYear('tanggal_proses', $filters['tahun']);
        }
        if (! empty($filters['bulan'])) {
            $query->whereMonth('tanggal_proses', $filters['bulan']);
        }

        return $query;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('humas')->logAll()->logOnlyDirty()->dontSubmitEmptyLogs();
    }
}
