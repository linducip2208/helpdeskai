<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->json('ai_classification')->nullable()->after('custom_fields');
            $table->string('ai_sentiment', 16)->nullable()->after('ai_classification');
            $table->timestamp('ai_classified_at')->nullable()->after('ai_sentiment');
        });

        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->string('sentiment', 16)->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['ai_classification', 'ai_sentiment', 'ai_classified_at']);
        });

        Schema::table('ticket_replies', function (Blueprint $table) {
            $table->dropColumn('sentiment');
        });
    }
};
