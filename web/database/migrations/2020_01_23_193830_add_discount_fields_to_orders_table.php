<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('coupon_id')->nullable()->unsigned()->after('tax');
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('set null');
            $table->double('discount_value')->nullable()->after('coupon_id');
            $table->string('discount_code')->nullable()->after('discount_value');
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
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['coupon_id', 'discount_value', 'discount_code']);
        });
    }
}
