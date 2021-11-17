<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountryTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('country_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('country_id')->unsigned()->index();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');

            $table->string('country_name');
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
        Schema::dropIfExists('country_translations');
    }
}
