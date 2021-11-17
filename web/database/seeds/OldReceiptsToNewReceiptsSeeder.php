<?php

use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OldReceiptsToNewReceiptsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try{
            $oldReceipts = DB::table('receipts')->get();
            if(count($oldReceipts) > 0){
                foreach($oldReceipts as $oldReceipt){
                    $order = Order::find($oldReceipt->order_id);
                    if($order->receipt_file == null){
                        $order->update(['receipt_file' => $oldReceipt->receipt]);
                    }
                    DB::table('receipts')->where('id', $oldReceipt->id)->delete();
                }
            }
        }catch(\Exception $error){
            print($error->getMessage());
        }
        
    }
}
