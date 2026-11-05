<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class LegalNotulensi extends Model
{
    use LogsActivity;

    protected $table = 'legal_notulensis';

    protected $guarded = ['id'];

    protected $casts = ['tanggal_rapat' => 'date'];

    public function activity()
    {
        return $this->belongsTo(LegalActivity::class, 'legal_activity_id');
    }

    public function document()
    {
        return $this->belongsTo(LegalDocument::class, 'legal_document_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('legal')->logAll()->logOnlyDirty()->dontSubmitEmptyLogs();
    }
}
