<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Voucher;
use App\Models\Image;

class OrderManagementOfUserController extends Controller
{
    
    public function getOrderInfoOfUser(Request $request){
        $orderStatusCode = $request->orderStatusCode;
        $startIndex = $request->startIndex;
        $itemQuantityEveryLoad = $request->itemQuantityEveryLoad;
        $userID = $request->userID;

        $infoOrder = Order::where([
            ['USER_ID', '=', $userID],
            ['STATUS', '=', $orderStatusCode]
        ])
        ->with(['orderDetails.fad'])
        ->withCount('orderDetails')
        ->orderBy('DATE', 'desc')
        ->skip($startIndex)
        ->take($itemQuantityEveryLoad)
        ->get()
        ->map(function ($order) {
            $firstOrderDetail = $order->orderDetails->first();
            $fadInfo = null;
            if ($firstOrderDetail && $firstOrderDetail->fad) {
                $fadInfo = [
                    'FAD_ID' => $firstOrderDetail->fad->FAD_ID,
                    'FAD_NAME' => $firstOrderDetail->fad->FAD_NAME,
                    'FAD_PRICE' => $firstOrderDetail->fad->FAD_PRICE,
                    'IMAGE_URL' => $firstOrderDetail->fad->image ? $firstOrderDetail->fad->image->URL : "https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/1_6_1702549349.png?alt=media&token=07bcfe4f-48fb-44e1-8681-1ccb27b31b59", 
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

        $deliveryInfo = Address::where('ADDRESS_ID', $orderInfo->ADDRESS_ID)->select('ADDRESS_ID', 'DETAIL', 'PHONE', 'NAME')->get();

        $orderDetailInfo = $orderInfo->with('orderDetails.fad')->where('ORDER_ID', $request->orderID)->get();

        $imageIds = [];

        // Duyệt qua kết quả để kiểm tra điều kiện
        foreach ($orderDetailInfo as $order) {
            foreach ($order->orderDetails as $detail) {
                if ($detail->fad && is_null($detail->fad->ID_PARENTFADOFTOPPING) && is_null($detail->fad->ID_PARENTFADOFSIZE)) {
                    // Lưu IMAGE_ID nếu thỏa mãn điều kiện
                    $imageIds[] = $detail->fad->IMAGE_ID;
                }
            }
        }

        $imagesURL = Image::whereIn('IMAGE_ID', $imageIds)->get();

        $voucherInfo = Voucher::where('VOUCHER_ID', $orderInfo->VOUCHER_ID)->select('VOUCHER_ID', 'VOUCHER_CODE', 'DISCOUNT');

        return response()->json([
            'statusCode' => 200,
            'deliveryInfo' => $deliveryInfo,
            'orderDetailInfo' => $orderDetailInfo,
            'voucherInfo' => $voucherInfo,
            'imagesURL' => $imagesURL,
            // 'data' => $data,
        ]);
    }

    public function changeOrderStatusToCancel(Request $request){
        $orderID = $request->orderID;
        $order = Order::where('ORDER_ID', $orderID)->first();
        $order->STATUS = 5;
        $order->save();

        return response()->json([
            'statusCode' => 200,
        ]);
    }
}
