<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConferenceRoomsTable extends Migration
{
    public function up()
    {
        Schema::create('conference_rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('capacity');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
