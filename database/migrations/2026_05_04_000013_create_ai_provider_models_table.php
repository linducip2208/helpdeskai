<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_provider_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('ai_providers')->cascadeOnDelete();
            $table->string('model_id', 200);
            $table->string('display_name', 200);
            $table->enum('capability', ['chat', 'reasoning', 'embedding', 'image', 'audio', 'vision']);
            $table->decimal('cost_input_per_1m', 10, 4)->nullable();
            $table->decimal('cost_output_per_1m', 10, 4)->nullable();
            $table->integer('max_tokens')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('provider_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_provider_models');
    }
};
