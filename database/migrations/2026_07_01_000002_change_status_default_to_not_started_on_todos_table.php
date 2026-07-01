<?php

use App\Models\Todo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeStatusDefaultToNotStartedOnTodosTable extends Migration
{
    public function up()
    {
        DB::table('todos')->whereNull('status')->update([
            'status' => Todo::STATUS_NOT_STARTED,
        ]);

        Schema::table('todos', function (Blueprint $table) {
            $table->string('status', 20)->default(Todo::STATUS_NOT_STARTED)->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->string('status', 20)->nullable()->default(null)->change();
        });
    }
}
