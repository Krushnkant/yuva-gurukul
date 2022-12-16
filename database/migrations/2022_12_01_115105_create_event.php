<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event', function (Blueprint $table) {
            $table->id();
            $table->text('event_title')->nullable();
            $table->text('event_image')->nullable();
            $table->text('event_description')->nullable();
            $table->dateTime('event_start_time')->nullable();
            $table->dateTime('event_end_time')->nullable();
            $table->integer('event_type')->nullable()->comment('1->Female,2->Male,3->Both');;
            $table->integer('estatus')->comment('1->Active,2->deactive,3->deleted');
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
        Schema::dropIfExists('event');
    }
}
