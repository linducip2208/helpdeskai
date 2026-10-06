<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->string('permissions')->default('read')->after('key');
            $table->unsignedBigInteger('usage_count')->default(0)->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn(['permissions', 'usage_count']);
        });
    }
};
