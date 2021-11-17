<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShippingAddressIdToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('shipping_address_id')->index()->unsigned()->nullable()->after('stripe_transaction_date');
            $table->foreign('shipping_address_id')->references('id')->on('user_addresses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shipping_address_id']);
            $table->dropColumn(['shipping_address_id']);
        });
    }
}
