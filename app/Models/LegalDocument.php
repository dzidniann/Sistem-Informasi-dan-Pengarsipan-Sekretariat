<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class LegalDocument extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'legal_documents';

    protected $fillable = [
        'name',
        'category_id',
        'description',
        'department',
        'pic',
        'effective_date',
        'expiry_date',
        'review_date',
        'status',
        'file_path',
        'version',
        'is_published',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'review_date' => 'date',
        'published_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'DRAFT';

    const STATUS_NEED_REVIEW = 'NEED_REVIEW';

    const STATUS_ACTIVE = 'ACTIVE';

    const STATUS_EXPIRING_SOON = 'EXPIRING_SOON';

    const STATUS_EXPIRED = 'EXPIRED';

    const DATE_MANAGED_STATUSES = [self::STATUS_ACTIVE, self::STATUS_EXPIRING_SOON, self::STATUS_EXPIRED];

    protected static function booted(): void
    {
        static::saving(function (self $document) {
            $document->status = $document->statusBasedOnExpiry();
        });
    }

    public function statusBasedOnExpiry(): string
    {
        if ($this->review_date) {
            // Expired documents take precedence over a scheduled review.
            if ($this->isExpired()) {
                return self::STATUS_EXPIRED;
            }
            if ($this->review_date->toDateString() <= today()->toDateString()) {
                return self::STATUS_NEED_REVIEW;
            }
        }

        if (!in_array($this->status, self::DATE_MANAGED_STATUSES, true)) {
            return $this->status ?? self::STATUS_DRAFT;
        }

        if ($this->isExpired()) {
            return self::STATUS_EXPIRED;
        }

        return $this->isExpiringSoon() ? self::STATUS_EXPIRING_SOON : self::STATUS_ACTIVE;
    }

    /**
     * Konfigurasi Activity Log Spatie
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function category()
    {
        return $this->belongsTo(LegalCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function histories()
    {
        return $this->hasMany(LegalDocumentHistory::class, 'document_id')->orderBy('created_at', 'desc');
    }

    /**
     * Scope: hanya dokumen yang sudah publish dan ACTIVE
     */
    public function scopePublished($query)
    {
        return $query
            ->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_EXPIRING_SOON])
            ->where('is_published', true)
            ->where(
                fn($dates) => $dates
                    ->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', today()->toDateString()),
            );
    }

    /**
     * Scope: filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Cek apakah dokumen bisa dipublish
     */
    public function canPublish(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_EXPIRING_SOON], true) && !$this->isExpired();
    }

    /**
     * Cek apakah dokumen sudah expired
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->toDateString() < today()->toDateString();
    }

    /**
     * Cek apakah dokumen akan segera expired (30 hari ke depan)
     */
    public function isExpiringSoon(): bool
    {
        return $this->expiry_date &&
            !$this->isExpired() &&
            $this->expiry_date->toDateString() <= today()->addDays(30)->toDateString();
    }
}
