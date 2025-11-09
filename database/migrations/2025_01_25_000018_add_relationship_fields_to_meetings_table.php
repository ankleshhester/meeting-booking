<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToMeetingsTable extends Migration
{
    public function up()
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->unsignedBigInteger('rooms_id')->nullable();
            $table->foreign('rooms_id', 'rooms_fk_10404592')->references('id')->on('conference_rooms');
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->foreign('created_by_id', 'created_by_fk_10404593')->references('id')->on('users');
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->foreign('owner_id', 'owner_fk_10404597')->references('id')->on('users');
        });
    }
}
