<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendeeMeetingMinutePivotTable extends Migration
{
    public function up()
    {
        Schema::create('attendee_meeting_minute', function (Blueprint $table) {
            $table->unsignedBigInteger('meeting_minute_id');
            $table->foreign('meeting_minute_id', 'meeting_minute_id_fk_10427957')->references('id')->on('meeting_minutes')->onDelete('cascade');
            $table->unsignedBigInteger('attendee_id');
            $table->foreign('attendee_id', 'attendee_id_fk_10427957')->references('id')->on('attendees')->onDelete('cascade');
        });
    }
}
