<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingMinutesTable extends Migration
{
    public function up()
    {
        Schema::create('meeting_minutes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->date('date');
            $table->time('start_time');
            $table->string('duration')->nullable();
            $table->time('end_time');
            $table->longText('description')->nullable();
            $table->string('meeting_mode')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
