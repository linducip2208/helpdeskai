<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_meta', function (Blueprint $table) {
            $table->string('url_pattern')->nullable()->after('id')->index();
            $table->longText('schema_json')->nullable()->after('canonical_url');
            $table->boolean('noindex')->default(false)->after('schema_json');

            $table->bigInteger('target_id')->nullable()->change();
            $table->string('target_type', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('seo_meta', function (Blueprint $table) {
            $table->dropColumn(['url_pattern', 'schema_json', 'noindex']);
        });
    }
};
