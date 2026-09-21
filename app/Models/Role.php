<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Role extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['nama'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Spatie Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nama',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}