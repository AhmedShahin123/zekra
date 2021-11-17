<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cart_id')->unsigned()->index();
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');

            $table->integer('album_id')->unsigned()->index();
            $table->foreign('album_id')->references('id')->on('albums')->onDelete('cascade');

            $table->integer('album_total');
            $table->float('album_total_fee');
            $table->float('album_total_tax');
            $table->float('total');
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
        Schema::dropIfExists('cart_details');
    }
}
