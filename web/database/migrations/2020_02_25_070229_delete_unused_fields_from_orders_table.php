<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteUnusedFieldsFromOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['stripe_transaction_date', 'coupon_id', 'discount_value', 'discount_code', 'card_token', 'stripe_id']);
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
            $table->string('stripe_transaction_date')->nullable()->after('courier_id');
            $table->bigInteger('coupon_id')->unsigned()->index()->nullable()->after('tax');
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('set null');
            $table->float('discount_value')->nullable();
            $table->string('discount_code')->nullable();
            $table->string('card_token')->nullable();
            $table->string('stripe_id')->nullable();
        });
    }
}
