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
        // $shop = Shop::where('SHOP_ID', $shopID)
        // ->with(['image', 'imageCover', 'address', 'user'])  
        // ->get()
        // ->map(function ($shop){
        //     return [
        //         'SHOP_ID' => $shop->SHOP_ID,
        //         'SHOP_NAME' => $shop->SHOP_NAME,
        //         'ADDRESS' => $shop->address ? $shop->address : "NULL",
        //         'PHONE' => $shop->PHONE,
        //         'AVT_IMAGE_URL' => $shop->image ? $shop->image->URL : "NULL",
        //         'COVER_IMAGE_URL' => $shop->imageCover ? $shop->imageCover->URL : "NULL",
        //         'SHOP_OWNER_ID' => $shop->user, 
        //         'DESCRIPTION' => $shop->DESCRIPTION,
        //         'IS_DELETED' => $shop->IS_DELETED, 
        //     ];
        // });
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

    public function getFADInfoAtHome(){
         //sql
        //  $FADInfo_sql = DB::select("SELECT 
        //                             FAD.FAD_ID, 
        //                             FAD.FAD_NAME, 
        //                             FAD.FAD_PRICE, 
        //                             IMAGE.URL AS FOOD_IMAGE_URL, 
        //                             SHOP.SHOP_NAME, 
        //                             SHOP_IMAGE.URL AS SHOP_IMAGE_URL
        //                         FROM 
        //                             FAD
        //                         LEFT JOIN 
        //                             IMAGE ON FAD.IMAGE_ID = IMAGE.IMAGE_ID
        //                         LEFT JOIN 
        //                             SHOP ON FAD.SHOP_ID = SHOP.SHOP_ID
        //                         LEFT JOIN 
        //                             IMAGE AS SHOP_IMAGE ON SHOP.AVT_IMAGE_ID = SHOP_IMAGE.IMAGE_ID;");
        //eloquent
        $FADInfo_eloquent = FAD::select('FAD_ID', 'FAD_NAME', 'FAD_PRICE')
                ->leftJoin('IMAGE', 'FAD.IMAGE_ID', '=', 'IMAGE.IMAGE_ID')
                ->leftJoin('SHOP', 'FAD.SHOP_ID', '=', 'SHOP.SHOP_ID')
                ->leftJoin('IMAGE AS SHOP_IMAGE', 'SHOP.AVT_IMAGE_ID', '=', 'SHOP_IMAGE.IMAGE_ID')
                ->addSelect('IMAGE.URL AS FOOD_IMAGE_URL', 'SHOP.SHOP_NAME', 'SHOP_IMAGE.URL AS SHOP_IMAGE_URL')
                ->get();

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
        $topping = FAD::where('ID_PARENTFADOFTOPPING', $request->FAD_ID)->get();
        
        return response()->json([
            'statusCode' => 200,
            'topping' => $topping,
        ]);
    }
}
