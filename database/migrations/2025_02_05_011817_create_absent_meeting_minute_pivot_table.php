<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absent_meeting_minute', function (Blueprint $table) {
            $table->unsignedBigInteger('meeting_minute_id');
            $table->foreign('meeting_minute_id', 'meeting_minute_id_fk_10427009')->references('id')->on('meeting_minutes')->onDelete('cascade');
            $table->unsignedBigInteger('attendee_id');
            $table->foreign('attendee_id', 'attendee_id_fk_10427009')->references('id')->on('attendees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absent_meeting_minute');
    }
};
