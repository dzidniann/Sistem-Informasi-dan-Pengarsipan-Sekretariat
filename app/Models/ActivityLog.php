<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['action', 'model_type', 'model_id', 'user_id', 'data_lama', 'data_baru'];

    protected $casts = [
        'created_at' => 'datetime',
        'data_lama' => 'array',
        'data_baru' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            $log->created_at = now();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * $dataLama: array data sebelum diubah (khusus edit)
     * $dataBaru: array data setelah ditambah/diubah
     */
    public static function catat(string $action, ?string $modelType = null, ?int $modelId = null, ?array $dataLama = null, ?array $dataBaru = null): void
    {
        static::create([
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'user_id' => auth()->id(),
            'data_lama' => $dataLama,
            'data_baru' => $dataBaru,
        ]);
    }

    /**
     * Hitung kolom mana saja yang benar-benar berubah nilainya,
     * dipakai untuk menandai bagian yang diedit di halaman detail.
     */
    public function kolomBerubah(): array
    {
        if (!$this->data_lama || !$this->data_baru) {
            return [];
        }

        $berubah = [];
        foreach ($this->data_baru as $kolom => $nilaiBaru) {
            $nilaiLama = $this->data_lama[$kolom] ?? null;
            if ($nilaiLama != $nilaiBaru) {
                $berubah[$kolom] = ['lama' => $nilaiLama, 'baru' => $nilaiBaru];
            }
        }

        return $berubah;
    }
}