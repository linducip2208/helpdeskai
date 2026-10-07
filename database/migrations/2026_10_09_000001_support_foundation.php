<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color', 16)->default('blue');
            $table->timestamps();
        });

        Schema::create('ticket_tag', function (Blueprint $table) {
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['ticket_id', 'tag_id']);
        });

        Schema::create('ticket_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('linked_ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->string('relation', 32)->default('related');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['ticket_id', 'linked_ticket_id', 'relation']);
            $table->index('linked_ticket_id');
        });

        Schema::create('watchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ticket_id', 'user_id']);
        });

        Schema::create('macros', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('visibility', 16)->default('shared');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('actions');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['visibility', 'is_active']);
        });

        Schema::create('sla_escalation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger', 64);
            $table->integer('after_minutes')->default(0);
            $table->string('action_priority', 16)->nullable();
            $table->string('action_assign_role', 64)->nullable();
            $table->boolean('notify_assignee')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sla_escalation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('sla_escalation_rules')->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->timestamp('ran_at')->useCurrent();
            $table->timestamps();

            $table->unique(['rule_id', 'ticket_id']);
        });

        Schema::create('automation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->nullable()->constrained('automation_rules')->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->string('trigger', 100)->nullable();
            $table->string('status', 16)->default('pending');
            $table->json('actions_executed')->nullable();
            $table->text('error')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('ticket_id');
        });

        Schema::table('sla_policies', function (Blueprint $table) {
            $table->boolean('pause_on_waiting')->default(true)->after('use_business_hours');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('sla_paused_seconds')->default(0)->after('sla_warned_at');
            $table->dateTime('sla_pause_started_at')->nullable()->after('sla_paused_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['sla_paused_seconds', 'sla_pause_started_at']);
        });

        Schema::table('sla_policies', function (Blueprint $table) {
            $table->dropColumn('pause_on_waiting');
        });

        Schema::dropIfExists('automation_runs');
        Schema::dropIfExists('sla_escalation_runs');
        Schema::dropIfExists('sla_escalation_rules');
        Schema::dropIfExists('macros');
        Schema::dropIfExists('watchers');
        Schema::dropIfExists('ticket_links');
        Schema::dropIfExists('ticket_tag');
        Schema::dropIfExists('tags');
    }
};
