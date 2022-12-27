<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfessionalDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('professional_details', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('type',255)->nullable();
            $table->string('title',255)->nullable();
            $table->string('education',255)->nullable();
            $table->string('address',255)->nullable();
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
        Schema::dropIfExists('professional_details');
    }
}
