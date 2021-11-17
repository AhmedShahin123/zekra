<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCurrencyTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currency_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('currency_id')->unsigned()->index();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');

            $table->string('currency_name');
            $table->string('locale');
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
        Schema::dropIfExists('currency_translations');
    }
}
