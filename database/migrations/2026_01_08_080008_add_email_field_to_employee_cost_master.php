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
            $table->string('email')->nullable()->after('emp_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_cost_master', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
