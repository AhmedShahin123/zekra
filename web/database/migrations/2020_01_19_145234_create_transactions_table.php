<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('zekra_payments');
            $table->float('partner_payments');
            $table->float('taxes');
            $table->float('shipping');
            $table->enum('payment_method', ['online_payment', 'COD'])->default('online_payment');
            $table->enum('collector', ['zekraHQ', 'partner'])->default('zekraHQ');
            $table->integer('country_id')->unsigned()->nullable()->index();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->integer('city_id')->unsigned()->nullable()->index();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->string('status');
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
        Schema::dropIfExists('transactions');
    }
}
