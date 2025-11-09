<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingMinuteUserPivotTable extends Migration
{
    public function up()
    {
        Schema::create('meeting_minute_user', function (Blueprint $table) {
            $table->unsignedBigInteger('meeting_minute_id');
            $table->foreign('meeting_minute_id', 'meeting_minute_id_fk_10427958')->references('id')->on('meeting_minutes')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id', 'user_id_fk_10427958')->references('id')->on('users')->onDelete('cascade');
        });
    }
}
