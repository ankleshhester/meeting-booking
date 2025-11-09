<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendeesTable extends Migration
{
    public function up()
    {
        Schema::create('attendees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('comment')->nullable();
            $table->string('response_status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
