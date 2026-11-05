<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalNotification extends Model
{
    use HasFactory;

    protected $table = 'legal_notifications';

    protected $fillable = ['user_id', 'type', 'message', 'priority', 'is_read'];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Type constants
     */
    const TYPE_EXPIRED_DOCUMENT = 'expired_document';

    const TYPE_UPCOMING_EXPIRATION = 'upcoming_expiration';

    const TYPE_REVIEW_REQUIRED = 'review_required';

    const TYPE_DOCUMENT_UPDATED = 'document_updated';

    const TYPE_NEW_CONSULTATION = 'new_consultation';

    const TYPE_CONSULTATION_SCHEDULE = 'consultation_schedule';

    /**
     * Priority constants
     */
    const PRIORITY_LOW = 'low';

    const PRIORITY_MEDIUM = 'medium';

    const PRIORITY_HIGH = 'high';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope: notifikasi yang belum dibaca
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: notifikasi milik user tertentu
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Tandai sebagai sudah dibaca
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Buat notifikasi baru
     */
    public static function kirim(int $userId, string $type, string $message, string $priority = 'medium'): self
    {
        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'priority' => $priority,
        ]);
    }
}
