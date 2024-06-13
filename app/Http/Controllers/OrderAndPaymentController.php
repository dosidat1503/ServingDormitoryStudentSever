<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\Order;
use App\Models\Order_detail; 

class OrderAndPaymentController extends Controller
{
    public function  getDefaultDeliveryInfo(Request $request){
        $userID = $request->userID;
        $defaultAddress = Address::select("ADDRESS_ID", "DETAIL", "PHONE", "NAME")
                                    ->where("USER_ID", $userID)
                                    ->where("IS_DEFAULT", 1)
                                    ->first();
        if($defaultAddress == null){
            $defaultAddress = Address::select("ADDRESS_ID", "DETAIL", "PHONE", "NAME")
                                    ->where("USER_ID", $userID)
                                    ->first();
        }

        return response()->json([
            'statusCode' => 200,
            'defaultAddress' => $defaultAddress,
        ]);
    }

    public function getDeliveryInfo(Request $request){
        $userID = $request->userID;
        $deliveryInfo = Address::select("ADDRESS_ID", "DETAIL", "PHONE", "NAME", "IS_DEFAULT")
                                    ->where("USER_ID", $userID)
                                    ->get();
        return response()->json([
            'statusCode' => 200,
            'deliveryInfo' => $deliveryInfo,
        ]);
    }

    public function saveOrder(Request $request){
        $ADDRESS_ID = $request->deliveryInfo['id'];
        $paymentMethod = $request->paymentMethod;
        $voucherID = $request->voucherID;
        $paymentAmount = $request->paymentAmount;
        $userID = $request->userID;
        $data = new \stdClass();
        $data->userID = $userID; 

        Order::create([
            'USER_ID' => $userID,
            'ORDER_ADDRESS_ID' => $ADDRESS_ID,
            'VOUCHER_ID' => $voucherID,
            'PAYMENT_METHOD' => $paymentMethod,
            'STATUS' => 1,
            'TOTAL_PAYMENT' => $paymentAmount, 
            'DATE' => now(),
        ]);

        $orderHasSaved =  Order::where('USER_ID', $userID)
                                ->orderBy('DATE', 'desc')
                                ->first();

        foreach($request->productList as $item){
            Order_detail::create([ 
                'ORDER_ID' => $orderHasSaved->ORDER_ID,
                'FAD_ID' => $item['id'],
                'QUANTITY' => $item['quantity'],
                'PRICE' => $item['price'],
            ]);

            $orderDetailHasSaved =  Order_detail::where('ORDER_ID', $orderHasSaved->ORDER_ID)->first();
            
            if(count($item['toppings']) > 0){
                $toppings = $item['toppings'];
                foreach($toppings as $topping){
                    Order_detail::create([
                        'ID_PARENT_OD_OF_THIS_OD' =>  $orderDetailHasSaved->ORDER_DETAIL_ID,
                        'ORDER_ID' => $orderHasSaved->ORDER_ID,
                        'FAD_ID' => $topping['id'],
                        'QUANTITY' => $topping['quantity'],
                        'PRICE' => $topping['price'],
                    ]);
                } 
            }
        }

        return response()->json([
            'statusCode' => 200,
            'message' => 'Order has been saved', 
            'data' => $data,
            'orderHasSaved' => $orderHasSaved
        ]);
    }
}
