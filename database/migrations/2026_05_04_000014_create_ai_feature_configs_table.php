<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_feature_configs', function (Blueprint $table) {
            $table->id();
            $table->string('feature_key', 100)->unique();
            $table->foreignId('provider_id')->nullable()->constrained('ai_providers')->nullOnDelete();
            $table->foreignId('model_id')->nullable()->constrained('ai_provider_models')->nullOnDelete();
            $table->json('options')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_feature_configs');
    }
};
