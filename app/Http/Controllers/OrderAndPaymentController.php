<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\Order;
use App\Models\Order_detail; 
use App\Models\Voucher; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\table;

class OrderAndPaymentController extends Controller
{
    public function getDefaultDeliveryInfo(Request $request){
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
        Log::info('Default Address:', [$defaultAddress ?? 'No default address found']);
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

    public function applyVoucher(Request $request){
        $voucher = $request->voucher; 
        $shopID = $request->shopID; 
        $totalMoneyForPayment = $request->totalMoneyForPayment; 
        $voucher = DB::table('voucher')
                    ->where('VOUCHER_CODE', $voucher)
                    ->where('SHOP_ID', $shopID)
                    ->first();
        if($voucher == null){
            return response()->json([
                'statusCode' => 404,
                'message' => 'Mã voucher sai hoặc không phải của shop đang đặt hàng',
            ]);
        }
        if($totalMoneyForPayment < $voucher->MIN_ORDER_TOTAL){
            return response()->json([
                'statusCode' => 404,
                'message' => 'Số tiền đơn hàng không đạt mức tối thiểu để sử dụng Voucher này',
            ]);
        }
        if($voucher->EXPIRATION_DATE < now()){
            return response()->json([
                'statusCode' => 404,
                'message' => 'Mã voucher này đã hết hạn sử dụng',
            ]);
        }
        if($voucher->START_DATE > now()){
            return response()->json([
                'statusCode' => 404,
                'message' => 'Mã voucher này chưa đến ngày có thể áp dụng được',
            ]);
        }
        

        return response()->json([
            'statusCode' => 200,
            'DISCOUNT_VALUE' => $voucher->DISCOUNT_VALUE,
            'voucher' => $voucher,
        ]);
    }

    public function saveOrder(Request $request){
        $ADDRESS_ID = $request->deliveryInfo["id"];
        $paymentMethod = $request->paymentMethod;
        $voucherCODE = $request->voucherCODE;
        $discountValue = $request->discountValue;
        $paymentTotal = $request->paymentTotal;
        $note = $request->note;
        $userID = $request->userID;
        $data = new \stdClass();
        $data->userID = $userID; 
        $productList = $request->productList;
        // lưu thông tin đơn hàng xuống trước

        $orderData = [
            'USER_ID' => $userID,
            'ADDRESS_ID' => $ADDRESS_ID,
            'PAYMENT_METHOD' => $paymentMethod,
            'PAYMENT_STATUS' => 0,
            'STATUS' => 1,
            'TOTAL_PAYMENT' => $paymentTotal, 
            'NOTE' => $note,
            'DATE' => now(),
        ];

        if ($voucherCODE != "" && $discountValue > 0) {
            $orderData = array_merge($orderData, [
                'VOUCHER_CODE' => $voucherCODE,
                'DISCOUNT_VALUE' => $discountValue,
            ]);
        }

        Log::info('Order Data:', $orderData);

        Order::create($orderData);

        // Order::create([
        //     'USER_ID' => $userID,
        //     'ADDRESS_ID' => $ADDRESS_ID,
        //     'VOUCHER_CODE' => $voucherCODE,
        //     'DISCOUNT_VALUE' => $discountValue,
        //     'PAYMENT_METHOD' => $paymentMethod,
        //     'PAYMENT_STATUS' => 0,
        //     'STATUS' => 1,
        //     'TOTAL_PAYMENT' => $paymentTotal, 
        //     'NOTE' => $note,
        //     'DATE' => now(),
        // ]);
        //lấy lên lại
        $orderHasSaved =  Order::where('USER_ID', $userID)
                                ->orderBy('DATE', 'desc')
                                ->first();
        //lưu thông tin FAD có trong đơn hàng
        foreach($productList as $item){
            $size = "";
            foreach($item['sizes'] as $sizeItem)
                $sizeItem['checked'] ? $size = $sizeItem['label'] : "";    
        }
            Order_detail::create([ 
                'ORDER_ID' => $orderHasSaved->ORDER_ID,
                'FAD_ID' => $item['id'],
                'QUANTITY' => $item['quantity'],
                'PRICE' => $item['price'],
                'SIZE' => $size, '',
            ]);

            $orderDetailHasSaved = Order_detail::orderBy('ORDER_DETAIL_ID', 'desc')->first();
            
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
        
        // chuyển khoản sẽ được tạo ở trạng thái đang chuẩn bị và đang giao.
        
        // if($paymentMethod == "Chuyển khoản"){
        //     $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        //     $vnp_Returnurl = "http://localhost:3000/payment";
        //     $vnp_TmnCode = "NCH1W7SL";//Mã website tại VNPAY 
        //     $vnp_HashSecret = "L4MNB0O55ENB6LWCOKW852OWZSWEF3G0"; //Chuỗi bí mật

        //     $vnp_TxnRef = 100; //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
        //     $vnp_OrderInfo = 'Thanh toán hoá đơn';
        //     $vnp_OrderType = 'Hoá đơn thời trang';
        //     $vnp_Amount = $paymentTotal * 100;
        //     $vnp_Locale = 'vn';
        //     $vnp_BankCode = '' ;
        //     $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
        //     //Add Params of 2.0.1 Version
        //     // $vnp_ExpireDate = $_POST['txtexpire'];
        //     //Billing
        //     // $vnp_Bill_Mobile = $numberPhone_ship;
        //     // $vnp_Bill_Email = $_POST['txt_billing_email'];
        //     // $fullName = trim($name_ship);
        //     // if (isset($fullName) && trim($fullName) != '') {
        //     //     $name = explode(' ', $fullName); 
        //     // } 
        //     // $vnp_Bill_Country=$_POST['txt_bill_country'];
        //     $vnp_Bill_State = 0; 
        //     $inputData = array(
        //         "vnp_Version" => "2.1.0",
        //         "vnp_TmnCode" => $vnp_TmnCode,
        //         "vnp_Amount" => $vnp_Amount,
        //         "vnp_Command" => "pay",  
        //         "vnp_CreateDate" => date('YmdHis'),
        //         "vnp_CurrCode" => "VND",
        //         "vnp_IpAddr" => $vnp_IpAddr,
        //         "vnp_Locale" => $vnp_Locale,
        //         "vnp_OrderInfo" => $vnp_OrderInfo,
        //         "vnp_OrderType" => $vnp_OrderType,
        //         "vnp_ReturnUrl" => $vnp_Returnurl,
        //         "vnp_TxnRef" => $vnp_TxnRef, 
        //         // "vnp_Bill_Mobile"=>$vnp_Bill_Mobile, 
        //         // "vnp_Bill_Address"=>$vnp_Bill_Address,
        //         // "vnp_Bill_City"=>$vnp_Bill_City, 
        //     );

        //     if (isset($vnp_BankCode) && $vnp_BankCode != "") {
        //         $inputData['vnp_BankCode'] = ($vnp_BankCode);
        //     }
        //     if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
        //         $inputData['vnp_Bill_State'] = ($vnp_Bill_State);
        //     }

        //     //var_dump($inputData);
        //     ksort($inputData);
        //     $query = "";
        //     $i = 0;
        //     $hashdata = "";
        //     foreach ($inputData as $key => $value) {
        //         if ($i == 1) {
        //             $hashdata .= '&' . urlencode($key) . "=" . urlencode(($value));
        //         } else {
        //             $hashdata .= urlencode(($key)) . "=" . urlencode(($value));
        //             $i = 1;
        //         }
        //         $query .= urlencode(($key)) . "=" . urlencode(($value)) . '&';
        //     }

        //     $vnp_Url = $vnp_Url . "?" . $query;
        //     if (isset($vnp_HashSecret)) {
        //         $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);//  
        //         $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        //     }
        //     $returnData = [
        //         'code' => '00', 
        //         'vnp_Url' => $vnp_Url,
        //         'statusCode' => 200,
        //         'message' => 'Order has been saved', 
        //         'data' => $data,
        //         'orderHasSaved' => $orderHasSaved
        //     ];
        //     if (isset($_POST['redirect'])) {
        //         header('Location: ' . $vnp_Url);
        //         die();
        //     } else {
        //         return response()->json([
        //             'data' => $returnData,
        //             'vnp_Url' => $vnp_Url,
        //         ]);
        //     } 
        // }
        else {
            return response()->json([
                'statusCode' => 200,
                'message' => 'Order has been saved', 
                'orderHasSaved' => $orderHasSaved
            ]);
        }
    }

    public function paymentOnline(Request $request){

        $ORDER_ID = $request->ORDER_ID;

        $orderInfo = DB::table('order')->where('ORDER_ID', $ORDER_ID)->first();
            

        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = "http://localhost:3000/payment";
        $vnp_TmnCode = "NCH1W7SL";//Mã website tại VNPAY 
        $vnp_HashSecret = "L4MNB0O55ENB6LWCOKW852OWZSWEF3G0"; //Chuỗi bí mật

        $vnp_TxnRef = $ORDER_ID; //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
        $vnp_OrderInfo = 'Thanh toán hoá đơn';
        $vnp_OrderType = 'Hoá đơn thời trang';
        $vnp_Amount = $orderInfo->TOTAL_PAYMENT * 100;
        $vnp_Locale = 'vn';
        $vnp_BankCode = '';
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
        //Add Params of 2.0.1 Version
        // $vnp_ExpireDate = $_POST['txtexpire'];
        //Billing
        // $vnp_Bill_Mobile = $numberPhone_ship;
        // $vnp_Bill_Email = $_POST['txt_billing_email'];
        // $fullName = trim($name_ship);
        // if (isset($fullName) && trim($fullName) != '') {
        //     $name = explode(' ', $fullName); 
        // } 
        // $vnp_Bill_Country=$_POST['txt_bill_country'];
        $vnp_Bill_State = 0; 
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",  
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef, 
            // "vnp_Bill_Mobile"=>$vnp_Bill_Mobile, 
            // "vnp_Bill_Address"=>$vnp_Bill_Address,
            // "vnp_Bill_City"=>$vnp_Bill_City, 
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = ($vnp_BankCode);
        }
        if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
            $inputData['vnp_Bill_State'] = ($vnp_Bill_State);
        }

        //var_dump($inputData);
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode(($value));
            } else {
                $hashdata .= urlencode(($key)) . "=" . urlencode(($value));
                $i = 1;
            }
            $query .= urlencode(($key)) . "=" . urlencode(($value)) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);//  
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        $returnData = [
            'code' => '00', 
            'vnp_Url' => $vnp_Url,
            'statusCode' => 200,
            'message' => 'Order has been saved',  
        ];
        if (isset($_POST['redirect'])) {
            header('Location: ' . $vnp_Url);
            die();
        } else {
            return response()->json([
                'data' => $returnData,
                'vnp_Url' => $vnp_Url,
            ]);
        } 
        
    }

    public function updateDeliveryInfo(Request $request){
        $userID = $request->userID;
        $address_id = $request->address_id;
        $name = $request->name;
        $phone = $request->phone;
        $address = $request->address;
        $isDefault = $request->isDefault;
        $isDefaultHasChanged = $request->isDefaultHasChanged;

        if($isDefaultHasChanged){
            Address::where('USER_ID', $userID)
                    ->update(['IS_DEFAULT' => null]);
        } 

        Address::where('ADDRESS_ID', $address_id)
                ->update([
                    'NAME' => $name,
                    'PHONE' => $phone,
                    'DETAIL' => $address,
                    'IS_DEFAULT' => $isDefault === 1 ? 1 : null,
                ]); 
        
        $afterSave = Address::get();

        return response()->json([
            'statusCode' => 200,
            'message' => 'Delivery info has been updated',
            'afterSave' => $afterSave,
        ]);
    }

}
