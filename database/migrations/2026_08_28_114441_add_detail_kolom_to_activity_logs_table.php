<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->json('data_lama')->nullable()->after('model_id');
            $table->json('data_baru')->nullable()->after('data_lama');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['data_lama', 'data_baru']);
        });
    }
};
