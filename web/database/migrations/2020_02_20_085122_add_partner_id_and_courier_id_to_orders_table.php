<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPartnerIdAndCourierIdToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('partner_id')->nullable()->index()->unsigned()->after('user_id');
            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('set null');

            $table->integer('courier_id')->nullable()->index()->unsigned()->after('partner_id');
            $table->foreign('courier_id')->references('id')->on('couriers')->onDelete('set null');
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
            $table->dropForeign(['partner_id']);
            $table->dropForeign(['courier_id']);
            $table->dropColumn(['partner_id', 'courier_id']);
        });
    }
}
