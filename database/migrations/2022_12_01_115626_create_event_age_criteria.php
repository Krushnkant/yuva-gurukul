<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventAgeCriteria extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_fees', function (Blueprint $table) {
            $table->id();
            $table->integer('event_id');
            $table->text('form_id')->nullable();
            $table->integer('from_age')->nullable();
            $table->integer('to_age')->nullable();
            $table->float('fees')->nullable();
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
        Schema::dropIfExists('event_fees');
    }
}
