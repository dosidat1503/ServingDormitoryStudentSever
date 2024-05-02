<?php

namespace App\Http\Controllers;

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
}
