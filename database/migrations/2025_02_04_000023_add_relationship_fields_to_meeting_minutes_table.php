<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToMeetingMinutesTable extends Migration
{
    public function up()
    {
        Schema::table('meeting_minutes', function (Blueprint $table) {
            $table->unsignedBigInteger('called_by_id')->nullable();
            $table->foreign('called_by_id', 'called_by_fk_10427959')->references('id')->on('users');
            $table->unsignedBigInteger('note_taker_id')->nullable();
            $table->foreign('note_taker_id', 'note_taker_fk_10427960')->references('id')->on('users');
            $table->unsignedBigInteger('meeting_id')->nullable();
            $table->foreign('meeting_id', 'meeting_fk_10427989')->references('id')->on('meetings');
            $table->unsignedBigInteger('rooms_id')->nullable();
            $table->foreign('rooms_id', 'rooms_fk_10427962')->references('id')->on('conference_rooms');
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->foreign('owner_id', 'owner_fk_10427967')->references('id')->on('users');
        });
    }
}
