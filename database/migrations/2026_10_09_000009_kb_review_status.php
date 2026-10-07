<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `knowledge_articles` MODIFY `status` ENUM('draft','review','published','archived') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `knowledge_articles` MODIFY `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft'");
        }
    }
};
