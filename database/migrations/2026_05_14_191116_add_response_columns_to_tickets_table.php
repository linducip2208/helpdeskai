<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('tickets', 'first_response_at')) {
                $table->timestamp('first_response_at')->nullable()->after('closed_at');
            }
            if (! Schema::hasColumn('tickets', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('first_response_at');
            }
            if (! Schema::hasColumn('tickets', 'satisfaction_rating')) {
                $table->unsignedTinyInteger('satisfaction_rating')->nullable()->after('resolved_at');
            }
            if (! Schema::hasColumn('tickets', 'satisfaction_comment')) {
                $table->text('satisfaction_comment')->nullable()->after('satisfaction_rating');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['first_response_at', 'resolved_at', 'satisfaction_rating', 'satisfaction_comment']);
        });
    }
};
