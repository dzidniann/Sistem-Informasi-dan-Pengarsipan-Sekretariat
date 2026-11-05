<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class LegalActivity extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    protected $casts = ['tanggal_kegiatan' => 'date'];

    public function document()
    {
        return $this->belongsTo(LegalDocument::class, 'legal_document_id');
    }

    public function notulensi()
    {
        return $this->hasMany(LegalNotulensi::class);
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
