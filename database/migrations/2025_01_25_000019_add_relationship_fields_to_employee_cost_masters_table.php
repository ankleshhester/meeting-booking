<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToEmployeeCostMastersTable extends Migration
{
    public function up()
    {
        Schema::table('employee_cost_masters', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->foreign('owner_id', 'owner_fk_10408358')->references('id')->on('users');
        });
    }
}
