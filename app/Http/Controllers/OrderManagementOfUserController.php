<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Voucher;

class OrderManagementOfUserController extends Controller
{
    
    public function getOrderInfoOfUser(Request $request){
        $orderStatusCode = $request->orderStatusCode;
        $startIndex = $request->startIndex;
        $endIndex = $request->endIndex;
        $userID = $request->userID;

        $infoOrder = Order::where([
            ['USER_ID', '=', $userID],
            ['STATUS', '=', $orderStatusCode]
        ])
        ->with(['orderDetails.fad'])
        ->withCount('orderDetails')
        ->orderBy('DATE', 'desc')
        ->skip(0)
        ->take(4)
        ->get()
        ->map(function ($order) {
            $firstOrderDetail = $order->orderDetails->first();
            $fadInfo = null;
            if ($firstOrderDetail && $firstOrderDetail->fad) {
                $fadInfo = [
                    'FAD_ID' => $firstOrderDetail->fad->FAD_ID,
                    'FAD_NAME' => $firstOrderDetail->fad->FAD_NAME,
                    'FAD_PRICE' => $firstOrderDetail->fad->FAD_PRICE,
                    'IMAGE_URL' => $firstOrderDetail->fad->image ? $firstOrderDetail->fad->image->URL : null, 
                ];
            }

            return [
                'ORDER_ID' => $order->ORDER_ID,
                'TOTAL_PAYMENT' => $order->TOTAL_PAYMENT,
                'PAYMENT_METHOD' => $order->PAYMENT_METHOD,
                'FAD_QUANTITY' => $order->order_details_count,
                'DATE' => $order->DATE, 
                'FAD_INFO' => $fadInfo,
            ];
        });

        // $infoOrder = Order::where('USER_ID', $userID) 
        // ->select('ORDER_ID', 'TOTAL_PAYMENT', 'PAYMENT_METHOD') 
        // ->get();

        return response()->json([
            'statusCode' => 200,
            'infoOrder' => $infoOrder,
        ]);
    }

    public function getOrderDetailInfo(Request $request){ 
        $orderInfo = Order::where('ORDER_ID', $request->orderID)->first();
        $data = new \stdClass();
        $data->orderInfo = $orderInfo; 

        $deliveryInfo = Address::where('ADDRESS_ID', $orderInfo->ORDER_ADDRESS_ID)->select('ADDRESS_ID', 'DETAIL', 'PHONE', 'NAME')->get();

        $orderDetailInfo = $orderInfo->with('orderDetails.fad')->where('ORDER_ID', $request->orderID)->get();

        $voucherInfo = Voucher::where('VOUCHER_ID', $orderInfo->VOUCHER_ID)->select('VOUCHER_ID', 'VOUCHER_CODE', 'DISCOUNT');

        return response()->json([
            'statusCode' => 200,
            'deliveryInfo' => $deliveryInfo,
            'orderDetailInfo' => $orderDetailInfo,
            'voucherInfo' => $voucherInfo,
            // 'data' => $data,
        ]);
    }
}
