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
        Schema::create('enrolee_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activated_user_id');            
            $table->string('nicare_id');            
            $table->string('sex');
            $table->string('phone');
            $table->unsignedBigInteger('lga');
            $table->unsignedBigInteger('ward');
            $table->unsignedBigInteger('facility_id');
            $table->string('service_accessed');
            $table->string('reason_for_visit');
            $table->date('date_of_visit');
            $table->string('reporting_month');
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
        Schema::dropIfExists('enrolee_visits');
    }
};
