<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ConsultationMessage extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'consultation_messages';

    protected $fillable = ['consultation_id', 'user_id', 'message', 'attachment'];

    /**
     * Konfigurasi Activity Log Spatie
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class, 'consultation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
