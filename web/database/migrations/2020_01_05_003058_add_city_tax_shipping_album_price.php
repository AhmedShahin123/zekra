<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCityTaxShippingAlbumPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->float('tax')->defualt(0);
            $table->float('shipping')->defualt(0);
            $table->float('album_price')->defualt(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('tax');
            $table->dropColumn('shipping');
            $table->dropColumn('album_price');
        });
    }
}
