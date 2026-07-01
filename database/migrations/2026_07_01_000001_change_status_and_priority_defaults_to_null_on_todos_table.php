<?php

use App\Models\Todo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeStatusAndPriorityDefaultsToNullOnTodosTable extends Migration
{
    public function up()
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->string('status', 20)->nullable()->default(null)->change();
            $table->string('priority', 20)->nullable()->default(null)->change();
        });
    }

    public function down()
    {
        DB::table('todos')->whereNull('status')->update([
            'status' => Todo::STATUS_NOT_STARTED,
        ]);
        DB::table('todos')->whereNull('priority')->update([
            'priority' => Todo::PRIORITY_MEDIUM,
        ]);

        Schema::table('todos', function (Blueprint $table) {
            $table->string('status', 20)->default(Todo::STATUS_NOT_STARTED)->nullable(false)->change();
            $table->string('priority', 20)->default(Todo::PRIORITY_MEDIUM)->nullable(false)->change();
        });
    }
}
