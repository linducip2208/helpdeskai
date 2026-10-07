<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndex('tickets', 'sla_due_at');
        $this->addIndex('tickets', 'sla_response_due_at');
        $this->addIndex('tickets', 'created_at');
        $this->addIndex('notifications', ['notifiable_type', 'notifiable_id']);
        $this->addIndex('notifications', 'read_at');
        $this->addIndex('ticket_replies', 'created_at');
        $this->addIndex('ticket_replies', 'user_id');
        $this->addIndex('ai_usage_logs', ['provider_id', 'created_at']);
    }

    public function down(): void
    {
        $this->dropIndexIfExists('ai_usage_logs', ['provider_id', 'created_at']);
        $this->dropIndexIfExists('ticket_replies', 'created_at');
        $this->dropIndexIfExists('ticket_replies', 'user_id');
        $this->dropIndexIfExists('notifications', ['notifiable_type', 'notifiable_id']);
        $this->dropIndexIfExists('notifications', 'read_at');
        $this->dropIndexIfExists('tickets', 'sla_due_at');
        $this->dropIndexIfExists('tickets', 'sla_response_due_at');
        $this->dropIndexIfExists('tickets', 'created_at');
    }

    protected function indexName(string $table, string|array $columns): string
    {
        $columns = (array) $columns;

        return $table.'_'.implode('_', $columns).'_index';
    }

    protected function existingIndexes(string $table): array
    {
        try {
            return array_map(
                fn ($index) => strtolower($index['name']),
                Schema::getIndexes($table)
            );
        } catch (Throwable) {
            return [];
        }
    }

    protected function addIndex(string $table, string|array $columns): void
    {
        if (in_array(strtolower($this->indexName($table, $columns)), $this->existingIndexes($table), true)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns) {
            $table->index($columns);
        });
    }

    protected function dropIndexIfExists(string $table, string|array $columns): void
    {
        if (! in_array(strtolower($this->indexName($table, $columns)), $this->existingIndexes($table), true)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns) {
            $table->dropIndex($columns);
        });
    }
};
