<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCashOnDeliveryFeesToCouriersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('couriers', function (Blueprint $table) {
            $table->double('cash_delivery_primary_amount')->nullable()->after('fee');
            $table->double('cash_delivery_primary_amount_fee')->nullable()->after('cash_delivery_primary_amount');
            $table->double('cash_delivery_additional_amount')->nullable()->after('cash_delivery_primary_amount_fee');
            $table->double('cash_delivery_additional_amount_fee')->nullable()->after('cash_delivery_additional_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('couriers', function (Blueprint $table) {
            $table->dropColumn('cash_delivery_primary_amount');
            $table->dropColumn('cash_delivery_primary_amount_fee');
            $table->dropColumn('cash_delivery_additional_amount');
            $table->dropColumn('cash_delivery_additional_amount_fee');
        });
    }
}
