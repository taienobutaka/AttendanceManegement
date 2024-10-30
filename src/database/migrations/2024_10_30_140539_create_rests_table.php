<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestsTable extends Migration
{
    public function up()
    {
        Schema::create('rests', function (Blueprint $table) {
            $table->id(); // unsigned bigint, primary key, not null
            $table->unsignedBigInteger('attendance_id'); // unsigned bigint, not null
            $table->time('start_time')->nullable(); // time
            $table->time('end_time')->nullable(); // time
            $table->timestamps(); // created_at, updated_at (timestamp)
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade'); // 外部キー制約
        });
    }

    public function down()
    {
        Schema::dropIfExists('rests');
    }
}