<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `tickets` MODIFY `status` ENUM('open','in_progress','waiting','answered','resolved','closed') NOT NULL DEFAULT 'open'");
            DB::statement("ALTER TABLE `ticket_replies` MODIFY `source` ENUM('web','email','chat','api','automation') NOT NULL DEFAULT 'web'");

            Schema::table('tickets', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
            });

            Schema::table('tickets', function (Blueprint $table) {
                $table->foreignId('department_id')->nullable()->change();
            });

            Schema::table('tickets', function (Blueprint $table) {
                $table->foreign('department_id')
                    ->references('id')
                    ->on('departments')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `ticket_replies` MODIFY `source` ENUM('web','email','chat','api') NOT NULL DEFAULT 'web'");
            DB::statement("ALTER TABLE `tickets` MODIFY `status` ENUM('open','in_progress','answered','resolved','closed') NOT NULL DEFAULT 'open'");
        }
    }
};
