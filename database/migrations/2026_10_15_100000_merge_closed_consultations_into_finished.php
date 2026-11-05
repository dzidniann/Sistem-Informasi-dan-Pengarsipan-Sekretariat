<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        // Keep timestamps and discussions intact so historical completion durations stay unchanged.
        DB::table('consultations')
            ->where('status', 'CLOSED')
            ->update(['status' => 'FINISHED']);
    }

    public function down(): void
    {
        // Cannot distinguish migrated consultations from those originally FINISHED.
        // Preserve their final status when rolling back rather than changing unrelated records.
    }
};
