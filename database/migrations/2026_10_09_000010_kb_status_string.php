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
            DB::statement("ALTER TABLE `knowledge_articles` MODIFY `status` VARCHAR(32) NOT NULL DEFAULT 'draft'");

            return;
        }

        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->string('status_temp', 32)->nullable();
        });

        DB::table('knowledge_articles')->update(['status_temp' => DB::raw('status')]);

        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->string('status', 32)->default('draft');
            $table->index('status');
        });

        DB::table('knowledge_articles')->whereNull('status')->update(['status' => 'draft']);
    }

    public function down(): void
    {
        // Intentionally not reverting to enum: string status is the supported format.
    }
};
