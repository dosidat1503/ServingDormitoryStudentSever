<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminShopController extends Controller
{
    public function createShop(Request $request)
    {
        try {
            $validatedReq = $request->validate([
                'shop_name' => 'required|string|max:191',
                'phone' => 'required|string|max:191',
                'opentime' => 'required|string',
                'closetime' => 'required|string',
                'avt_image_id' => 'integer|nullable|exists:image,IMAGE_ID',
                'cover_image_id' => 'required|nullable|exists:image,IMAGE_ID',
                'shop_owner_id' => 'required|integer|exists:user,USER_ID',
                'address_id' => 'integer|nullable|exists:address,ADDRESS_ID',
                'description' => 'string|nullable|max:500',
            ]);

            $shop = Shop::create([
                'SHOP_NAME' => $request->shop_name,
                'PHONE' => $request->phone,
                'OPENTIME' => $request->opentime,
                'CLOSETIME' => $request->closetime,
                'AVT_IMAGE_ID' => $request->avt_image_id,
                'COVER_IMAGE_ID' => $request->cover_image_id,
                'SHOP_OWNER_ID' => $request->shop_owner_id,
                'ADDRESS_ID' => $request->address_id,
                'DESCRIPTION' => $request->description,
                'created_at' => Carbon::now(),
                'IS_DELETED' => 0
            ]);
            return response()->json(
                [
                    'data' => $shop,
                    'message' => 'Tạo shop thành công',
                    'statusCode' => 201,
                ]
            );
        } catch (ValidationException $e) {
            // Handle validation failure
            return response()->json(
                [
                    'errors' => $e->errors(),
                    'message' => 'Validation failed',
                    'statusCode' => 422,
                ],
                422
            );
        }
    }

    public function getShopDetail(Request $request, $id)
    {
        $shop = DB::select("SELECT 
                                S.SHOP_ID,
                                S.SHOP_NAME,
                                S.PHONE,
                                S.OPENTIME,
                                S.CLOSETIME,
                                S.AVT_IMAGE_ID,
                                IA.URL AS AVATAR_IMAGE_URL,
                                S.COVER_IMAGE_ID,
                                IC.URL AS COVER_IMAGE_URL,
                                S.SHOP_OWNER_ID,
                                US.EMAIL,
                                S.ADDRESS_ID,
                                CONCAT(AC.DETAIL, \", \", AC.COMMUNE, \", \", AC.DISTRICT, \", \", AC.PROVINCE) AS ADDRESS,
                                S.DESCRIPTION,
                                S.IS_DELETED,
                                S.created_at
                            FROM
                            shop S
                        LEFT JOIN 
                            image IA ON S.AVT_IMAGE_ID = IA.IMAGE_ID
                        LEFT JOIN 
                            image IC ON S.COVER_IMAGE_ID = IC.IMAGE_ID
                        LEFT JOIN 
                            address AC ON S.ADDRESS_ID = AC.ADDRESS_ID
                        LEFT JOIN
                            user US ON S.SHOP_OWNER_ID = US.USER_ID
                        WHERE s.SHOP_ID = {$id}");
        if (!$shop) {
            return response()->json(
                [
                    'data' => null,
                    'message' => 'Không tìm thấy shop',
                    'statusCode' => 404,
                ]
            );
        }

        return response()->json(
            [
                'data' => $shop[0],
                'message' => 'Lấy thông tin shop thành công',
                'statusCode' => 200,
            ]
        );
    }

    public function updateShopDetail(Request $request,  $id)
    {
        $shop = DB::select("SELECT 
                            S.SHOP_ID,
                            S.SHOP_NAME,
                            S.PHONE,
                            S.OPENTIME,
                            S.CLOSETIME,
                            S.AVT_IMAGE_ID,
                            IA.URL AS AVATAR_IMAGE_URL,
                            S.COVER_IMAGE_ID,
                            IC.URL AS COVER_IMAGE_URL,
                            S.SHOP_OWNER_ID,
                            US.EMAIL,
                            S.ADDRESS_ID,
                            CONCAT(AC.DETAIL, \", \", AC.COMMUNE, \", \", AC.DISTRICT, \", \", AC.PROVINCE) AS ADDRESS,
                            S.DESCRIPTION,
                            S.IS_DELETED,
                            S.created_at
                                    FROM
                                    shop S
                                LEFT JOIN 
                                    image IA ON S.AVT_IMAGE_ID = IA.IMAGE_ID
                                LEFT JOIN 
                                    image IC ON S.COVER_IMAGE_ID = IC.IMAGE_ID
                                LEFT JOIN 
                                    address AC ON S.ADDRESS_ID = AC.ADDRESS_ID
                                LEFT JOIN
                                    user US ON S.SHOP_OWNER_ID = US.USER_ID
                                WHERE s.SHOP_ID = {$id}")[0];

        if (empty(get_object_vars($shop))) {
            return response()->json(
                [
                    'data' => null,
                    'message' => 'Not found or maybe had been deleted',
                    'statusCode' => 404,
                ]
            );
        }
        try {
            $validatedReq = $request->validate([
                'admin_id' => 'integer|required',
                'shop_name' => 'required|string|max:191',
                'phone' => 'required|string|max:191',
                'opentime' => 'required|string',
                'closetime' => 'required|string',
                'avt_image_link' => 'required|string',
                'cover_image_link' => 'required|string',
                'address_id' => 'integer|nullable|exists:address,ADDRESS_ID',
                'description' => 'string|nullable|max:500',
            ]);
            if (strcmp($request->avt_image_link, $shop->AVATAR_IMAGE_URL) === 0) {
                $avt_id = $shop->AVT_IMAGE_ID;
            } else {
                $createAvt = Image::create([
                    'URL' => $request->avt_image_link,
                    'USER_ID' => $request->admin_id,
                ]);
                $avt_id = $createAvt['IMAGE_ID'];
            }
            if (strcmp($request->cover_image_link, $shop->COVER_IMAGE_URL) === 0) {
                $cover_id = $shop->COVER_IMAGE_ID;
            } else {
                $createCov = Image::create([
                    'URL' => $request->avt_image_link,
                    'USER_ID' => $request->admin_id,
                ]);
                $cover_id = $createCov['IMAGE_ID'];
            }

            $updateShop = Shop::where('SHOP_ID', $id)->where('IS_DELETED', 0)->first();

            $updateShop->update([
                'SHOP_NAME' => $request->shop_name,
                'PHONE' => $request->phone,
                'OPENTIME' => $request->opentime,
                'CLOSETIME' => $request->closetime,
                'AVT_IMAGE_ID' => $avt_id,
                'COVER_IMAGE_ID' => $cover_id,
                'ADDRESS_ID' => $request->address_id,
                'DESCRIPTION' => $request->description,
            ]);

            return response()->json(
                [
                    'data' => $updateShop,
                    'message' => 'Cập nhật thông tin shop thành công',
                    'statusCode' => 201,
                ]
            );
        } catch (ValidationException $e) {
            // Handle validation failure
            return response()->json(
                [
                    'errors' => $e->errors(),
                    'message' => 'Validation failed',
                    'statusCode' => 422,
                ],
                422
            );
        }
    }

    public function deleteShop(Request $request, $id)
    {
        $shop = Shop::where('SHOP_ID', $id)->where('IS_DELETED', 0)->first();
        if (!$shop) {
            return response()->json(
                [
                    'data' => null,
                    'message' => 'Không tìm thấy shop',
                    'statusCode' => 404,
                ]
            );
        }

        $shop->IS_DELETED = 1;
        $shop->save();

        return response()->json(
            [
                'data' => $shop,
                'message' => 'Xoá shop thành công',
                'statusCode' => 200,
            ]
        );
    }
}
