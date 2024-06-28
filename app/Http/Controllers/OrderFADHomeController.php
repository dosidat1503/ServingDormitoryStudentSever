<?php

namespace App\Http\Controllers;

use App\Models\FAD;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\dd;
use Illuminate\Support\Facades\DB;



class OrderFADHomeController extends Controller
{
    public function getFADShop(){
        // $shop = Shop::with(['image'])->get(['SHOP_ID', 'SHOP_NAME'])->map(function ($shop){
        //     return [
        //         'SHOP_ID' => $shop->SHOP_ID,
        //         'SHOP_NAME' => $shop->SHOP_NAME,
        //         'IMAGE_URL' => $shop->image ? $shop->image->URL : "NULL",
        //         'USER_ID' => $shop->user ? $shop->user->USER_ID : "NULL",
        //     ];
        // }); 
        $shop = DB::select("SELECT
                                shop.SHOP_ID, 
                                shop.SHOP_NAME, 
                                image.URL AS IMAGE_URL 
                            FROM 
                                shop 
                            LEFT JOIN 
                                image ON shop.AVT_IMAGE_ID = image.IMAGE_ID");
        // $shop = Shop::with(['image' => function($query) {
        //     $query->select('IMAGE_ID', 'URL');
        // }])
        // ->select('SHOP_ID', 'SHOP_NAME', 'AVT_IMAGE_ID')
        // ->get();
        return response()->json([
            'statusCode' => 200,
            'shop' => $shop,
        ]);
    }

    public function getFADShopDetailInfo(Request $request){
        $shopID = $request->shop_id; 
        $shop = DB::table('SHOP')
        ->where('SHOP_ID', $shopID)
        ->get()
        ->map(function ($shop) {
            $address = DB::table('ADDRESS')->where('ADDRESS_ID', $shop->ADDRESS_ID)->first();
            $user = DB::table('USER')->where('USER_ID', $shop->SHOP_OWNER_ID)->first();
            $image = DB::table('IMAGE')->where('IMAGE_ID', $shop->AVT_IMAGE_ID)->first();
            $imageCover = DB::table('IMAGE')->where('IMAGE_ID', $shop->COVER_IMAGE_ID)->first();

            return [
                'SHOP_ID' => $shop->SHOP_ID,
                'SHOP_NAME' => $shop->SHOP_NAME,
                'ADDRESS' => $address ? $address : "NULL",
                'PHONE' => $shop->PHONE,
                'AVT_IMAGE_URL' => $image ? $image->URL : "NULL",
                'COVER_IMAGE_URL' => $imageCover ? $imageCover->URL : "NULL", 
                'DESCRIPTION' => $shop->DESCRIPTION,
                'IS_DELETED' => $shop->IS_DELETED, 
            ];
        });
 
        return response()->json([
            'statusCode' => 200,
            'shop' => $shop,
        ]);
    }

    public function getFADInfo(Request $request){
        $FADShop_ID = $request->FADShop_ID;
        $atHome = $request->atHome;

        $FADInfo_eloquent = FAD::select('FAD.FAD_ID', 'FAD.FAD_NAME', 'FAD.FAD_PRICE', 'FAD.CATEGORY')
            ->leftJoin('IMAGE', 'FAD.IMAGE_ID', '=', 'IMAGE.IMAGE_ID')
            ->leftJoin('SHOP', 'FAD.SHOP_ID', '=', 'SHOP.SHOP_ID')
            ->leftJoin('IMAGE AS SHOP_IMAGE', 'SHOP.AVT_IMAGE_ID', '=', 'SHOP_IMAGE.IMAGE_ID')
            ->where([
                ['FAD.ID_PARENTFADOFTOPPING', null],
                ['FAD.ID_PARENTFADOFSIZE', null]
            ])
            ->addSelect('IMAGE.URL AS FOOD_IMAGE_URL', 'SHOP.SHOP_NAME', 'SHOP_IMAGE.URL AS SHOP_IMAGE_URL');

        if ($FADShop_ID != 0) {
            $FADInfo_eloquent = $FADInfo_eloquent->where('FAD.SHOP_ID', $FADShop_ID);
        } 
        
        if($atHome == 1){
            $FADInfo_eloquent = $FADInfo_eloquent
            ->leftJoin('order_detail', 'FAD.FAD_ID', '=', 'ORDER_DETAIL.FAD_ID')
            ->addSelect(DB::raw('COUNT(order_detail.FAD_ID) AS ORDER_COUNT'))
            ->groupBy('FAD.FAD_ID', 'FAD.FAD_NAME', 'FAD.FAD_PRICE', 'FAD.CATEGORY', 'IMAGE.URL', 'SHOP.SHOP_NAME', 'SHOP_IMAGE.URL')
            ->orderBy('ORDER_COUNT', 'desc')
            ->limit(10); 
        }

        $FADInfo_eloquent = $FADInfo_eloquent->get(); 
        
        
        $shopInfo = Shop::select('SHOP_ID', 'SHOP_NAME')
        ->leftJoin('image', 'shop.AVT_IMAGE_ID', '=', 'image.IMAGE_ID')
        ->addSelect('image.URL AS SHOP_IMAGE_URL')
        ->get();

        return response()->json([
            'statusCode' => 200,
            // 'ShopInfo_sql' => $ShopInfo_sql,
            'FADInfo_eloquent' => $FADInfo_eloquent,
            'shopInfo' => $shopInfo,
        ]);
    }

    public function getFADDetailInfo(Request $request){
        // $topping = FAD::where('ID_PARENTFADOFTOPPING', $request->FAD_ID)->get();
        $topping = DB::table('FAD')
                ->leftJoin('IMAGE', 'FAD.IMAGE_ID', '=', 'IMAGE.IMAGE_ID')
                ->select('FAD.FAD_ID', 'FAD.FAD_NAME', 'FAD.FAD_PRICE', 'IMAGE.URL AS TOPPING_URL')
                ->where('ID_PARENTFADOFTOPPING', $request->FAD_ID)
                ->get();
                
        $size = FAD::where('ID_PARENTFADOFSIZE', $request->FAD_ID)->get();
        
        return response()->json([
            'statusCode' => 200,
            'topping' => $topping,
            'size' => $size,
        ]);
    }

    public function userSearchFAD(Request $request){
        $textToSearchFAD = $request->textToSearchFAD;
        $range = $request->range;//range là array
        $FADtype = $request->FADtype;
        $deliveryType = $request->deliveryType;//điều kiện này sẽ xem xét dùng sau
        $sortType = $request->sortType;
        $tagIDToGetFADInfo = $request->tagIDToGetFADInfo;

        $FADInfo_eloquent = FAD::select('FAD.FAD_ID', 'FAD_NAME', 'FAD_PRICE')
        ->leftJoin('IMAGE', 'FAD.IMAGE_ID', '=', 'IMAGE.IMAGE_ID')
        ->leftJoin('SHOP', 'FAD.SHOP_ID', '=', 'SHOP.SHOP_ID')
        ->leftJoin('IMAGE AS SHOP_IMAGE', 'SHOP.AVT_IMAGE_ID', '=', 'SHOP_IMAGE.IMAGE_ID')
        ->addSelect('IMAGE.URL AS FOOD_IMAGE_URL', 'SHOP.SHOP_NAME', 'SHOP_IMAGE.URL AS SHOP_IMAGE_URL');

        if ($textToSearchFAD != "") {
            $FADInfo_eloquent = $FADInfo_eloquent->where('FAD_NAME', 'like', '%' . $textToSearchFAD . '%');
        }
        if($tagIDToGetFADInfo != 0){
            $FADInfo_eloquent = $FADInfo_eloquent->where('TAG', '=', $tagIDToGetFADInfo);
        }
        if ($FADtype != 0) {
            $FADInfo_eloquent = $FADInfo_eloquent->where('CATEGORY', '=', $FADtype);
        }
        if (!empty($range) && is_array($range)) {
            $FADInfo_eloquent = $FADInfo_eloquent->whereBetween('FAD_PRICE', [$range[0], $range[1]]);
        } 
        if($sortType == 1){
            $FADInfo_eloquent = $FADInfo_eloquent
            ->leftJoin('order_detail', 'FAD.FAD_ID', '=', 'ORDER_DETAIL.FAD_ID')
            ->addSelect(DB::raw('COUNT(order_detail.FAD_ID) AS ORDER_COUNT'))
            ->groupBy('FAD.FAD_ID', 'FAD.FAD_NAME', 'FAD.FAD_PRICE', 'IMAGE.URL', 'SHOP.SHOP_NAME', 'SHOP_IMAGE.URL')
            ->orderBy('ORDER_COUNT', 'desc');
            // $FADInfo_eloquent = $FADInfo_eloquent->addSelect([
            //     DB::raw('(SELECT COUNT(*) FROM ORDER_DETAIL WHERE ORDER_DETAIL.FAD_ID = FAD.FAD_ID) as order_count')
            // ])->orderBy('order_count', 'desc');
        }
        else if($sortType == 2){
            $FADInfo_eloquent = $FADInfo_eloquent->orderBy('DATE', 'asc');
        } 

        $FADInfo_eloquent = $FADInfo_eloquent->get();


        return response()->json([
            'statusCode' => 200, 
            'FADInfo_eloquent' => $FADInfo_eloquent, 
        ]);
    }
}
