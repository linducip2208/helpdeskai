<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['identified', 'investigating', 'mitigated', 'resolved', 'closed'])->default('identified');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('root_cause')->nullable();
            $table->text('resolution')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'severity']);
        });

        Schema::create('incident_ticket', function (Blueprint $table) {
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->primary(['incident_id', 'ticket_id']);
        });

        Schema::create('problems', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('root_cause')->nullable();
            $table->text('symptoms')->nullable();
            $table->text('workaround')->nullable();
            $table->text('permanent_fix')->nullable();
            $table->enum('status', ['open', 'investigating', 'resolved', 'closed'])->default('open');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('knowledge_article_id')->nullable()->constrained('knowledge_articles')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('problem_ticket', function (Blueprint $table) {
            $table->foreignId('problem_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->primary(['problem_id', 'ticket_id']);
        });

        Schema::create('problem_incident', function (Blueprint $table) {
            $table->foreignId('problem_id')->constrained()->cascadeOnDelete();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->primary(['problem_id', 'incident_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('problem_incident');
        Schema::dropIfExists('problem_ticket');
        Schema::dropIfExists('problems');
        Schema::dropIfExists('incident_ticket');
        Schema::dropIfExists('incidents');
    }
};
