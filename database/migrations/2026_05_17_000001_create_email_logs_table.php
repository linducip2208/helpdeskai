<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('to_email');
            $table->string('subject');
            $table->longText('body_plain')->nullable();
            $table->longText('body_html')->nullable();
            $table->string('message_id')->nullable()->index();
            $table->string('in_reply_to')->nullable()->index();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->foreignId('reply_id')->nullable()->constrained('ticket_replies')->nullOnDelete();
            $table->enum('direction', ['inbound', 'outbound'])->default('inbound');
            $table->enum('status', ['received', 'parsed', 'failed', 'sent', 'queued'])->default('received');
            $table->text('error')->nullable();
            $table->json('headers')->nullable();
            $table->integer('attachments_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'direction']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
