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
        Schema::table('meetings', function (Blueprint $table) {
            $table->boolean('is_master')
                ->default(false)
                ->after('meeting_mode');

            $table->foreignId('parent_meeting_id')
                ->nullable()
                ->after('is_master')
                ->constrained('meetings')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropForeign(['parent_meeting_id']);
            $table->dropColumn(['is_master', 'parent_meeting_id']);
        });
    }
};
