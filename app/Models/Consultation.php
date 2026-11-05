<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Consultation extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'consultations';

    protected $fillable = ['user_id', 'title', 'description', 'attachment', 'status', 'assigned_legal', 'schedule'];

    protected $casts = [
        'schedule' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_WAITING_REVIEW = 'WAITING_REVIEW';

    const STATUS_ACCEPTED = 'ACCEPTED';

    const STATUS_SCHEDULED = 'SCHEDULED';

    const STATUS_ONGOING = 'ONGOING';

    const STATUS_FINISHED = 'FINISHED';

    /**
     * Konfigurasi Activity Log Spatie
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedLegal()
    {
        return $this->belongsTo(User::class, 'assigned_legal');
    }

    public function messages()
    {
        return $this->hasMany(ConsultationMessage::class, 'consultation_id')->orderBy('created_at', 'asc');
    }

    /**
     * Scope: filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: konsultasi milik user tertentu
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: konsultasi yang sudah dijadwalkan
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('schedule')->whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_ONGOING]);
    }
}
