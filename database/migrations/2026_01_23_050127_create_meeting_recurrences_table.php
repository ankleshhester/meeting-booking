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
        Schema::create('meeting_recurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();

            $table->enum('frequency', ['daily', 'weekly', 'monthly']);
            $table->integer('interval')->default(1); // every 1 week, every 2 weeks, etc.

            $table->json('days_of_week')->nullable(); // ["mon","wed"]
            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->integer('occurrences')->nullable(); // alternative to end_date
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_recurrences');
    }
};
