<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id(); // unsigned bigint, primary key, not null
            $table->unsignedBigInteger('user_id'); // unsigned bigint, not null
            $table->date('date'); // date, not null
            $table->time('start_time')->nullable(); // time
            $table->time('end_time')->nullable(); // time
            $table->timestamps(); // created_at, updated_at (timestamp)
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}