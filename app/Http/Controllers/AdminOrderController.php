<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\Shop;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminOrderController extends Controller
{
    public function getAllUserOrders(Request $request)
    {
        // Lấy thông tin trạng thái đơn hàng, số lượng mỗi lần load và index
        $orderStatusCode = $request->orderStatusCode;
        $itemQuantityEveryLoad = $request->itemQuantityEveryLoad;
        $startIndex = $request->startIndex;

        $infoOrder = Order::where([
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

    public function getOrderDetailInfo(Request $request, $id)
    {
        $orderInfo = Order::where('ORDER_ID', $id)->first();
        $data = new \stdClass();
        $data->orderInfo = $orderInfo;

        $deliveryInfo = Address::where('ADDRESS_ID', $orderInfo->ORDER_ADDRESS_ID)->select('ADDRESS_ID', 'DETAIL', 'PHONE', 'NAME')->first();

        $orderDetailInfo = $orderInfo->with('orderDetails.fad')->where('ORDER_ID', $id)->get();

        $voucherInfo = Voucher::where('VOUCHER_ID', $orderInfo->VOUCHER_ID)->select('VOUCHER_ID', 'VOUCHER_CODE', 'DISCOUNT_VALUE', 'SHOP_ID')->first();
        $shopInfo = Shop::where('SHOP_ID', $voucherInfo->SHOP_ID)->select('SHOP_NAME')->first();

        return response()->json([
            'statusCode' => 200,
            'deliveryInfo' => $deliveryInfo,
            'orderDetailInfo' => $orderDetailInfo,
            'voucherInfo' => $voucherInfo,
            'shopInfo' => $shopInfo,
        ]);
    }

    public function updateOrderStatus(Request $request, $id)
    { // Xác thực yêu cầu
        $validator = Validator::make($request->all(), [
            'status_code' => 'required|integer|in:1,2,3,4,5',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Tìm đơn hàng
        $order = Order::findOrFail($id);

        // Cập nhật trạng thái
        $order->status = $request->status_code;
        $order->save();

        // Trả về phản hồi
        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order,
        ]);
    }
}
