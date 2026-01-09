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
        Schema::table('employee_cost_masters', function (Blueprint $table) {
            $table->renameColumn('cost_per_hour', 'ctc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_cost_masters', function (Blueprint $table) {
            $table->renameColumn('ctc', 'cost_per_hour');
        });
    }
};
