<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeCostMastersTable extends Migration
{
    public function up()
    {
        Schema::create('employee_cost_masters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('emp_code')->unique();
            $table->decimal('cost_per_hour', 15, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
