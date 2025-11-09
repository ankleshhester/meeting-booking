<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToAttendeesTable extends Migration
{
    public function up()
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->foreign('owner_id', 'owner_fk_10404583')->references('id')->on('users');
        });
    }
}
