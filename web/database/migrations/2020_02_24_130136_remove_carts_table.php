<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function(Blueprint $table){
            $table->dropForeign(['cart_id']);
            $table->dropColumn('cart_id');
        });
        Schema::dropIfExists('cart_details');
        Schema::dropIfExists('carts');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('cart_total');
            $table->float('cart_total_fee');
            $table->float('cart_total_tax');
            $table->float('total_price');

            $table->timestamps();
        });

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

        Schema::table('orders', function(Blueprint $table){
            $table->integer('cart_id')->unsigned()->nullable()->after('id');
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('set null');
        });
    }
}
