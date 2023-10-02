<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medical_bills', function (Blueprint $table) {
            $table->id();
            $table->string('month');
            $table->float('amount');
            $table->float('main_amount')->nullable();
            $table->float('remaining_amount')->nullable();
            $table->unsignedBigInteger('facility_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
