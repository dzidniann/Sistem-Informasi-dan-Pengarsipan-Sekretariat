<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, LogsActivity, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role_id', 'bidang_id', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    /**
     * Konfigurasi Activity Log Spatie
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'email', 'role_id', 'bidang_id', 'is_active'])->logOnlyDirty();
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function bisaAksesSemuaBidang(): bool
    {
        if (in_array($this->role?->nama, ['sekretariat', 'humas'], true)) {
            return false;
        }

        return (bool) $this->bidang?->akses_semua_bidang;
    }

    public function hasRole($role)
    {
        return $this->role->nama === $role;
    }

    /**
     * Relasi: konsultasi yang diajukan oleh user ini
     */
    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'user_id');
    }

    /**
     * Relasi: konsultasi yang di-assign ke user ini (legal admin)
     */
    public function assignedConsultations()
    {
        return $this->hasMany(Consultation::class, 'assigned_legal');
    }

    /**
     * Relasi: dokumen hukum yang dibuat oleh user ini
     */
    public function legalDocuments()
    {
        return $this->hasMany(LegalDocument::class, 'created_by');
    }

    /**
     * Relasi: notifikasi hukum milik user ini
     */
    public function legalNotifications()
    {
        return $this->hasMany(LegalNotification::class, 'user_id');
    }

    /**
     * Hitung notifikasi yang belum dibaca
     */
    public function unreadLegalNotificationsCount(): int
    {
        return $this->legalNotifications()->where('is_read', false)->count();
    }
}
