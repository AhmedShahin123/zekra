<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->string('stripe_transaction_date')->nullable()->change();
            $table->enum('progress_status', ['Received', 'To do','In Progress','Finsnihed'])->default('Received')->after('total');
            $table->enum('delivery_status', ['Initiated', 'Out to delivery','Delivered'])->default('Initiated')->after('progress_status');
            $table->string('progress_status_date')->nullable()->after('delivery_status');
            $table->string('delivery_status_date')->nullable()->after('progress_status_date');
            $table->string('payment_status')->default('Not Paid')->after('delivery_status_date');
            $table->string('card_token')->nullable();
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
            $table->dropColumn('progress_status');
            $table->dropColumn('delivery_status');
            $table->dropColumn('progress_status_date');
            $table->dropColumn('delivery_status_date');
            $table->dropColumn('payment_status');
        });
    }
}
