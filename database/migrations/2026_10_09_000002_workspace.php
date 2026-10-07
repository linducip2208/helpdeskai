<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['team_id', 'user_id']);
        });

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'organization_id')) {
                $table->foreignId('organization_id')->nullable()->after('phone')->constrained('organizations')->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'vip')) {
                $table->boolean('vip')->default(false)->after('organization_id');
            }
            if (! Schema::hasColumn('users', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('vip');
            }
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event', 64);
            $table->json('channels')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'event']);
            $table->index('event');
        });

        Schema::create('saved_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_shared')->default(false);
            $table->json('filters')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_shared']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_views');
        Schema::dropIfExists('notification_preferences');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'organization_id')) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            }
            if (Schema::hasColumn('users', 'vip')) {
                $table->dropColumn('vip');
            }
            if (Schema::hasColumn('users', 'internal_notes')) {
                $table->dropColumn('internal_notes');
            }
        });

        Schema::dropIfExists('organizations');
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
    }
};
