<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropScheduleColumnsFromTodosTable extends Migration
{
    public function up()
    {
        Schema::table('todos', function (Blueprint $table) {
            if (Schema::hasColumn('todos', 'scheduled_start_at')) {
                $table->dropColumn('scheduled_start_at');
            }

            if (Schema::hasColumn('todos', 'scheduled_end_at')) {
                $table->dropColumn('scheduled_end_at');
            }
        });
    }

    public function down()
    {
        Schema::table('todos', function (Blueprint $table) {
            if (!Schema::hasColumn('todos', 'scheduled_start_at')) {
                $table->dateTime('scheduled_start_at')->nullable()->after('content');
            }

            if (!Schema::hasColumn('todos', 'scheduled_end_at')) {
                $table->dateTime('scheduled_end_at')->nullable()->after('scheduled_start_at');
            }
        });
    }
}
