<?php

use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateOldOrderPartnersAndCouriers extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try{
            $oldPartners = DB::table('partner_orders')->get();
            if(count($oldPartners) > 0){
                foreach($oldPartners as $oldPartner){
                    $order = Order::find($oldPartner->order_id);
                    if($order->partner_id == null){
                        $order->update(['partner_id' => $oldPartner->partner_id]);
                    }
                    // DB::table('receipts')->where('id', $oldReceipt->id)->delete();
                }
            }

            $oldCouriers = DB::table('courier_orders')->get();
            if(count($oldCouriers) > 0){
                foreach($oldCouriers as $oldCourier){
                    $order = Order::find($oldCourier->order_id);
                    if($order->courier_id == null){
                        $order->update(['courier_id' => $oldCourier->courier_id]);
                    }
                    // DB::table('receipts')->where('id', $oldReceipt->id)->delete();
                }
            }
        }catch(\Exception $error){
            print($error->getMessage());
        }
    }
}
