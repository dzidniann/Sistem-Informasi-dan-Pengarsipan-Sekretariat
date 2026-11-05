<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HumasCsrAttachment extends Model
{
    protected $guarded = ['id'];

    public function csr()
    {
        return $this->belongsTo(HumasCsr::class, 'humas_csr_id');
    }
}
