<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryIdToExistingTodosTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('todos', 'category_id')) {
            Schema::table('todos', function (Blueprint $table) {
                $table->foreignId('category_id')->after('id')->constrained()->cascadeOnDelete();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('todos', 'category_id')) {
            Schema::table('todos', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }
    }
}
