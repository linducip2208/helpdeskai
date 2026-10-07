<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qa_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reply_id')->unique()->constrained('ticket_replies')->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('accuracy')->nullable();
            $table->unsignedTinyInteger('completeness')->nullable();
            $table->unsignedTinyInteger('tone')->nullable();
            $table->unsignedTinyInteger('empathy')->nullable();
            $table->unsignedTinyInteger('policy_compliance')->nullable();
            $table->unsignedTinyInteger('knowledge_correctness')->nullable();
            $table->float('overall')->nullable();
            $table->text('feedback')->nullable();
            $table->string('provider', 100)->nullable();
            $table->string('model', 200)->nullable();
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->timestamps();

            $table->index('ticket_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qa_scores');
    }
};
