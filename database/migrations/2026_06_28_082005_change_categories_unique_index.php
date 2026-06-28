<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if ($indexName = $this->uniqueIndexName(['name'])) {
            Schema::table('categories', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        }

        if (! $this->uniqueIndexName(['user_id', 'name'])) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unique(['user_id', 'name']);
            });
        }
    }

    public function down(): void
    {
        if ($indexName = $this->uniqueIndexName(['user_id', 'name'])) {
            Schema::table('categories', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        }

        if (! $this->uniqueIndexName(['name'])) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unique('name');
            });
        }
    }

    private function uniqueIndexName(array $columns): ?string
    {
        foreach (Schema::getIndexes('categories') as $index) {
            if (($index['unique'] ?? false) && $index['columns'] === $columns) {
                return $index['name'];
            }
        }

        return null;
    }
};
