<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LegalGovernanceDocument extends Model
{
    use LogsActivity;

    protected $fillable = ['judul_file', 'tanggal', 'file_path', 'original_name', 'created_by'];

    protected $casts = ['tanggal' => 'date'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('legal')->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }
}
