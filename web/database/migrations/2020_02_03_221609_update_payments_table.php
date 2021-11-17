<?php

use App\Models\Payment;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_method')->after('purchased_id');
            $table->string('card_token')->nullable()->change();
            $table->string('payment_provider')->nullable()->change();
            $table->string('payment_provider_id')->nullable()->change();
        });

        DB::table('payments')->update(['payment_method' => 'cred_card']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('payment_method');
            $table->string('card_token')->nullable(false)->change();
            $table->string('payment_provider')->nullable(false)->change();
            $table->string('payment_provider_id')->nullable(false)->change();
        });
    }
}
