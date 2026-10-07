<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->json('tags')->nullable()->after('content');
            $table->string('language', 8)->default('id')->after('tags');
            $table->json('metadata')->nullable()->after('language');
            $table->string('embedding_status', 32)->default('none')->after('metadata');
            $table->timestamp('published_at')->nullable()->after('embedding_status');
        });
    }

    public function down(): void
    {
        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->dropColumn(['tags', 'language', 'metadata', 'embedding_status', 'published_at']);
        });
    }
};
