<?php

use App\Models\Todo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusAndPriorityToTodosTable extends Migration
{
    public function up()
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->string('status', 20)->default(Todo::STATUS_NOT_STARTED)->after('content');
            $table->string('priority', 20)->default(Todo::PRIORITY_MEDIUM)->after('status');
        });
    }

    public function down()
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->dropColumn(['status', 'priority']);
        });
    }
}
