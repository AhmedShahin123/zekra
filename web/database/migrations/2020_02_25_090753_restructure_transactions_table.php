<?php

use App\Models\Transaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RestructureTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // create a json backup file from all the old transactions
        $transactions = Transaction::all();
        $jsonData = json_encode($transactions);
        file_put_contents(public_path('/json/backup/transactions.json'), $jsonData);

        // drop the transactions table 
        Schema::dropIfExists('transactions');

        // create the transaction table with the new structure
        Schema::create('transactions', function(Blueprint $table){
            $table->bigIncrements('id');
            $table->bigInteger('payment_id')->unsigned()->index();
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
            $table->string('type');
            $table->float('amount');
            $table->timestamps();
        });

        // seed the old transactions data in the table
        $oldTransactionsJson = file_get_contents(public_path('/json/backup/transactions.json'));
        $oldTransactions = json_decode($oldTransactionsJson);
        $types = [
            'zekra_payments'    => 'zekra_payments',
            'partner_payments'  => 'partner_payments',
            'taxes'             => 'taxes',
            'shipping'          => 'shipping',
            'cod_fees'          => 'cod'
        ];
        foreach($oldTransactions as $oldTransaction){
            foreach($types as $key => $type){
                if($oldTransaction->payment_id !== null && $oldTransaction->{$key} !== null){
                    $transactionData = [
                        'payment_id'    => $oldTransaction->payment_id,
                        'type'          => $type,
                        'amount'        => $oldTransaction->{$key},
                        'created_at'    => $oldTransaction->created_at,
                        'updated_at'    => $oldTransaction->updated_at
                    ];
                    DB::table('transactions')->insert($transactionData);
                }
            }
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // drop the transactions table 
        Schema::dropIfExists('transactions');

        // create the transaction table with the old structure
        Schema::create('transactions', function(Blueprint $table){
            $table->bigIncrements('id');
            $table->bigInteger('payment_id')->unsigned()->index()->nullable();
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
            $table->float('zekra_payments');
            $table->float('partner_payments');
            $table->float('taxes');
            $table->float('shipping');
            $table->float('cod_fees')->nullable();
            $table->enum('payment_method', ['online_payment', 'COD'])->default('online_payment');
            $table->enum('collector', ['zekraHQ', 'partner'])->default('zekraHQ');
            $table->integer('country_id')->unsigned()->nullable()->index();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->integer('city_id')->unsigned()->nullable()->index();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->string('status');
            $table->timestamps();
        });

        // seed the old transactions data in the table
        $oldTransactionsJson = file_get_contents(public_path('/json/backup/transactions.json'));
        $oldTransactions = json_decode($oldTransactionsJson);
        foreach($oldTransactions as $oldTransaction){
            $transactionData = [
                'payment_id'        => $oldTransaction->payment_id,
                'zekra_payments'    => $oldTransaction->zekra_payments,
                'partner_payments'  => $oldTransaction->partner_payments,
                'taxes'             => $oldTransaction->taxes,
                'shipping'          => $oldTransaction->shipping,
                'cod_fees'          => $oldTransaction->cod_fees,
                'payment_method'    => $oldTransaction->payment_method,
                'collector'         => $oldTransaction->collector,
                'country_id'        => $oldTransaction->country_id,
                'city_id'           => $oldTransaction->city_id,
                'status'            => $oldTransaction->status,
                'created_at'        => $oldTransaction->created_at,
                'updated_at'        => $oldTransaction->updated_at,
            ];
            DB::table('transactions')->insert($transactionData);
        }
    }
}
