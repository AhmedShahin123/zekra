<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCourierPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courier_prices', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('courier_id')->unsigned()->index();
            $table->foreign('courier_id')->references('id')->on('couriers')->onDelete('cascade');

            $table->integer('zone')->unsigned();

            $table->double('primary_weight');
            $table->double('primary_weight_price');

            $table->double('additional_weight');
            $table->double('additional_weight_price');

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
        Schema::dropIfExists('courier_prices');
    }
}
