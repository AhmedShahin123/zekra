<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusFieldToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('amount', 'money_amount');
            $table->integer('points_amount')->after('amount');
            $table->boolean('status')->after('payment_provider_id');
            $table->text('extra_data')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('money_amount', 'amount');
            $table->dropColumn(['points_amount', 'status', 'extra_data']);
        });
    }
}
