<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendeeMeetingPivotTable extends Migration
{
    public function up()
    {
        Schema::create('attendee_meeting', function (Blueprint $table) {
            $table->unsignedBigInteger('meeting_id');
            $table->foreign('meeting_id', 'meeting_id_fk_10404590')->references('id')->on('meetings')->onDelete('cascade');
            $table->unsignedBigInteger('attendee_id');
            $table->foreign('attendee_id', 'attendee_id_fk_10404590')->references('id')->on('attendees')->onDelete('cascade');
        });
    }
}
