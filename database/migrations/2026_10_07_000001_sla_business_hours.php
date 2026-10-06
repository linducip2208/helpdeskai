<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sla_policies', function (Blueprint $table) {
            $table->json('workdays')->nullable()->after('resolution_time');
            $table->time('work_start')->default('08:00')->after('workdays');
            $table->time('work_end')->default('17:00')->after('work_start');
            $table->string('timezone', 64)->default('Asia/Jakarta')->after('work_end');
            $table->boolean('use_business_hours')->default(true)->after('timezone');
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dateTime('sla_response_due_at')->nullable()->after('sla_due_at');
            $table->dateTime('sla_warned_at')->nullable()->after('sla_response_due_at');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['sla_response_due_at', 'sla_warned_at']);
        });

        Schema::dropIfExists('holidays');

        Schema::table('sla_policies', function (Blueprint $table) {
            $table->dropColumn(['workdays', 'work_start', 'work_end', 'timezone', 'use_business_hours']);
        });
    }
};
