// database/migrations/0001_01_01_000027_update_user_devices_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_devices', function (Blueprint $table) {
            if (!Schema::hasColumn('user_devices', 'fcm_token')) {
                $table->string('fcm_token')->nullable()->after('device_id');
            }
            if (!Schema::hasColumn('user_devices', 'last_used_at')) {
                $table->timestamp('last_used_at')->nullable()->after('is_active');
            }
        });
    }

    public function down()
    {
        Schema::table('user_devices', function (Blueprint $table) {
            $table->dropColumn(['fcm_token', 'last_used_at']);
        });
    }
};