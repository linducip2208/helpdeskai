<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_providers', function (Blueprint $table) {
            $table->integer('priority')->default(0)->after('is_active');
            $table->integer('timeout_seconds')->default(60)->after('priority');
            $table->integer('max_retries')->default(2)->after('timeout_seconds');
            $table->string('organization', 255)->nullable()->after('max_retries');
        });

        Schema::table('ai_provider_models', function (Blueprint $table) {
            $table->integer('context_window')->nullable()->after('max_tokens');
            $table->integer('max_output_tokens')->nullable()->after('context_window');
            $table->integer('priority')->default(0)->after('is_active');
        });

        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $table->foreignId('fallback_from_provider_id')->nullable()->after('model_id')->constrained('ai_providers')->nullOnDelete();
            $table->integer('attempt_no')->default(1)->after('fallback_from_provider_id');
        });

        Schema::create('ai_budgets', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 32);
            $table->string('scope_id', 191)->nullable();
            $table->string('period', 16);
            $table->decimal('limit_usd', 12, 4);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['scope', 'scope_id', 'period']);
            $table->index(['scope', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_budgets');

        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fallback_from_provider_id');
            $table->dropColumn('attempt_no');
        });

        Schema::table('ai_provider_models', function (Blueprint $table) {
            $table->dropColumn(['context_window', 'max_output_tokens', 'priority']);
        });

        Schema::table('ai_providers', function (Blueprint $table) {
            $table->dropColumn(['priority', 'timeout_seconds', 'max_retries', 'organization']);
        });
    }
};
