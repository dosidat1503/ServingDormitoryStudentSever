<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

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
        ->skip($startIndex)
        ->take($endIndex - $startIndex)
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
}
