<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique()->change;
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->unique();
            $table->string('avatar')->nullable();
            $table->integer('country_id')->unsigned()->nullable()->index();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->integer('city_id')->unsigned()->nullable()->index();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->text('address')->nullable();
            $table->string('password')->change;
            $table->boolean('status')->nullable();
            $table->string('token')->nullable();
            $table->string('reset_code')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
