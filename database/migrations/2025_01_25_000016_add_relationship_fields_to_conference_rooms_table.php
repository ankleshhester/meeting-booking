<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToConferenceRoomsTable extends Migration
{
    public function up()
    {
        Schema::table('conference_rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('building_area_id')->nullable();
            $table->foreign('building_area_id', 'building_area_fk_10404567')->references('id')->on('buildings_areas');
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->foreign('owner_id', 'owner_fk_10404574')->references('id')->on('users');
        });
    }
}
