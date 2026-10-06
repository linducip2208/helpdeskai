<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->text('body');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'answered', 'resolved', 'closed'])->default('open');
            $table->enum('source', ['web', 'email', 'chat', 'api'])->default('web');
            $table->dateTime('sla_due_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->boolean('is_starred')->default(false);
            $table->json('custom_fields')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('priority');
            $table->index('source');
            $table->index('closed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
