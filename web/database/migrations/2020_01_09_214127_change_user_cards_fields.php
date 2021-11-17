<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUserCardsFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_cards', function (Blueprint $table) {
            $table->dropColumn('card_token');
            $table->integer('exp_month')->after('user_id');
            $table->integer('exp_year')->after('exp_month');
            $table->integer('last4')->after('exp_year');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_cards', function (Blueprint $table) {
            $table->string('card_token')->nullable()->after('user_id');
            $table->dropColumn('exp_month');
            $table->dropColumn('exp_year');
            $table->dropColumn('last4');
        });
    }
}
