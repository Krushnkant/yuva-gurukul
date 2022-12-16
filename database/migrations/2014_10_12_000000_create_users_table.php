<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('role', [1,2,3])->nullable()->comment('1->Admin,2->Sub Admin,3->End User');
            $table->integer('zone_id');
            $table->integer('parent_id');
            $table->integer('family_parent_id');
            $table->text('first_name')->nullable();
            $table->text('middle_name')->nullable();
            $table->text('last_name')->nullable();
            $table->text('email')->nullable();
            $table->text('password')->nullable();
            $table->text('decrypted_password')->nullable();
            $table->rememberToken();
            $table->text('mobile_no')->nullable();
            $table->text('profile_pic')->nullable();
            $table->text('address')->nullable();
            $table->dateTime('birth_date')->nullable();
            $table->integer('gender')->default(1)->comment('1->Female,2->Male,3->Other');
            $table->integer('estatus')->default(1)->comment('1->Active,2->Deactive,3->Deleted,4->Pending');
            $table->timestamp('email_verified_at')->nullable();
            $table->dateTime('created_at')->default(\Carbon\Carbon::now());
            $table->dateTime('updated_at')->default(null)->onUpdate(\Carbon\Carbon::now());
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
