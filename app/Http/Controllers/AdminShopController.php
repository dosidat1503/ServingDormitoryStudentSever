<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        return response()->json(
            [
                'data' => $shop,
                'message' => 'Lấy thông tin shop thành công',
                'statusCode' => 200,
            ]
        );
    }

    public function updateShopDetail(Request $request,  $id)
    {
        $shop = Shop::where('SHOP_ID', $id)->where('IS_DELETED', 0)->first();

        if (!$shop) {
            return response()->json(
                [
                    'data' => null,
                    'message' => 'Không tìm thấy cửa hàng',
                    'statusCode' => 404,
                ]
            );
        }
        try {
            $validatedReq = $request->validate([
                'shop_name' => 'required|string|max:191',
                'phone' => 'required|string|max:191',
                'opentime' => 'required|string',
                'closetime' => 'required|string',
                'avt_image_id' => 'integer|nullable|exists:image,IMAGE_ID',
                'cover_image_id' => 'required|nullable|exists:image,IMAGE_ID',
                'address_id' => 'integer|nullable|exists:address,ADDRESS_ID',
                'description' => 'string|nullable|max:500',
            ]);

            $shop->update([
                'SHOP_NAME' => $request->shop_name,
                'PHONE' => $request->phone,
                'OPENTIME' => $request->opentime,
                'CLOSETIME' => $request->closetime,
                'AVT_IMAGE_ID' => $request->avt_image_id,
                'COVER_IMAGE_ID' => $request->cover_image_id,
                'ADDRESS_ID' => $request->address_id,
                'DESCRIPTION' => $request->description,
            ]);

            return response()->json(
                [
                    'data' => $shop,
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
