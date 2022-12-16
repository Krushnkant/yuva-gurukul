<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestKaryaKarta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_karya_karta', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('request_by_user_id')->nullable();
            $table->dateTime('date_time')->nullable();
            $table->enum('estatus', [1,2,3])->default(1)->comment('1->Pending,2->Approve,3->Reject');
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
        Schema::dropIfExists('request_karya_karta');
    }
}
