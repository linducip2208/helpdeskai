<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kb_article_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->text('excerpt')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });

        Schema::create('kb_searches', function (Blueprint $table) {
            $table->id();
            $table->string('query', 191);
            $table->integer('results_count')->default(0);
            $table->string('language', 8)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index('query');
            $table->index('created_at');
        });

        Schema::create('ai_queries', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->text('answer')->nullable();
            $table->float('confidence')->nullable();
            $table->json('sources')->nullable();
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->integer('latency_ms')->nullable();
            $table->decimal('cost_estimated', 10, 6)->default(0);
            $table->string('feedback', 16)->nullable();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index('feedback');
            $table->index('created_at');
        });

        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32);
            $table->string('filename');
            $table->integer('total_rows')->default(0);
            $table->integer('imported')->default(0);
            $table->integer('failed')->default(0);
            $table->json('errors')->nullable();
            $table->string('status', 16)->default('pending');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_logs');
        Schema::dropIfExists('ai_queries');
        Schema::dropIfExists('kb_searches');
        Schema::dropIfExists('kb_article_revisions');
    }
};
