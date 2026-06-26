<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSortOrderToTodosTable extends Migration
{
    public function up()
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0);
        });

        DB::table('todos')->orderBy('id')->pluck('id')->each(function ($id, $index) {
            DB::table('todos')
                ->where('id', $id)
                ->update(['sort_order' => $index + 1]);
        });
    }

    public function down()
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
}
