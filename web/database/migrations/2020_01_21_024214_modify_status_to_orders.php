<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyStatusToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('progress_status', ['Order Received', 'PDF Generated','Job Assigned to Partner','Printed','Lamination','UV coating','Cutting','Perforation','Creasing','Binding','Trimming','Packing','Ready for Pickup'])->default('Order Received')->after('total');
            $table->enum('delivery_status', ['Ready for Pickup', 'Out to delivery','Undelivered','Delivered'])->default('Ready for Pickup')->after('progress_status');
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
        });
    }
}
