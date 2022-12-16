<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventBooking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_booking', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('event_id')->nullable();
            $table->float('amount')->nullable();
            $table->string('total_person')->nullable();
            $table->dateTime('atten_time')->nullable();
            $table->enum('is_present',[0, 1])->nullable();
            $table->integer('QR_scan_by')->nullable();
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
        Schema::dropIfExists('event_booking');
    }
}
