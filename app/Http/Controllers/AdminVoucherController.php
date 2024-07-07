<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminVoucherController extends Controller
{
    public function getAllVouchers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $shopId = $request->shop_id;
        $index = $request->index;

        $vouchers = Voucher::where([
            ['SHOP_ID', '=', $shopId],
            ['IS_DELETED', '=', 0]
        ])->paginate(10, ['*'], 'page', $index);

        $deletedVouchers = Voucher::where([
            ['SHOP_ID', '=', $shopId],
            ['IS_DELETED', '=', 1]
        ])->get();

        return response()->json([
            "data" => [
                "vouchers" => $vouchers,
                "deleted_vouchers" => $deletedVouchers
            ],
            "message" => "Lấy danh sách voucher thành công",
            "statusCode" => 200
        ]);
    }

    public function addVoucher(Request $request)
    {
        $shopId = $request->shop_id;
        $voucherCode = $request->voucher_code;
        $voucher = Voucher::where([
            ['VOUCHER_CODE', '=', "$voucherCode"],
            ['IS_DELETED', '=', 0],
            ['SHOP_ID', '=', $shopId]
        ])->first();

        if (!is_null($voucher)) {
            return response()->json(
                [
                    'data' => $voucher,
                    'message' => 'Mã khuyến mãi này đã được tạo',
                    'statusCode' => 409,
                ]
            );
        }
        try {
            $this->validate($request, [
                'voucher_code' => 'required|string|max:191',
                'shop_id' => 'required|integer',
                'discount_value' => 'required|integer',
                'min_order_total' => 'required|numeric',
                'start_date' => 'required|string',
                'end_date' => 'required|string',
                'max_quantity' => 'required|integer'
            ]);

            $newVoucher = Voucher::create([
                'VOUCHER_CODE' => $request->voucher_code,
                'SHOP_ID' => $request->shop_id,
                'DISCOUNT_VALUE' => $request->discount_value,
                'MIN_ORDER_TOTAL' => $request->min_order_total,
                'START_DATE' => $request->start_date,
                'EXPIRATION_DATE' => $request->end_date,
                'MAX_QUANTITY' => $request->max_quantity,
                'IS_DELETED' => 0
            ]);

            return response()->json([
                "data" => $newVoucher,
                "message" => "Tạo voucher thành công",
                "statusCode" => 200,
            ], 200);
        } catch (ValidationException $e) {
            // Handle validation failure
            return response()->json(
                [
                    'errors' => $e->errors(),
                    'message' => 'Xác thực thất bại',
                    'statusCode' => 422,
                ],
                422
            );
        }
    }

    public function getDetailVoucher(Request $request, $id)
    {
        $voucher = Voucher::where("VOUCHER_ID", $id)->where('IS_DELETED', 0)->first();

        if (is_null($voucher)) {
            return response()->json(
                [
                    'data' => $voucher,
                    'message' => 'Không tìm thấy voucher',
                    'statusCode' => 404,
                ]
            );
        }

        return response()->json(
            [
                'data' => $voucher,
                'message' => 'Lấy thông tin voucher thành công',
                'statusCode' => 200,
            ]
        );
    }

    public function searchVoucher(Request $request)
    {
        $voucherCode = $request->voucher_code;
        $shopId = $request->shop_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $query = Voucher::select('VOUCHER.*')
            ->leftJoin('SHOP AS S', 'VOUCHER.SHOP_ID', '=', 'S.SHOP_ID')->where("S.SHOP_ID", "=", $shopId);

        if ($voucherCode != "") {
            $query->where('VOUCHER_CODE', 'like', '%' . $voucherCode . '%');
        }

        if (!empty($startDate)) {
            $query->where('START_DATE', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->where('EXPIRATION_DATE', '<=', $endDate);
        }

        $voucherEloquent = $query->get();

        return response()->json([
            'statusCode' => 200,
            'data' => $voucherEloquent,
        ]);
    }

    public function updateVoucher(Request $request, $id)
    {
        $voucher = Voucher::where('VOUCHER_ID', $id)->where('IS_DELETED', 0)->first();

        if (empty(get_object_vars($voucher)) || is_null($voucher)) {
            return response()->json(
                [
                    'data' => $voucher,
                    'message' => 'Không tìm thấy voucher',
                    'statusCode' => 404,
                ]
            );
        }

        try {
            $this->validate($request, [
                'voucher_code' => 'required|string|max:191',
                'discount_value' => 'required|integer',
                'min_order_total' => 'required|numeric',
                'start_date' => 'required|string',
                'end_date' => 'required|string',
                'max_quantity' => 'required|integer'
            ]);

            $voucher->update([
                'VOUCHER_CODE' => $request->voucher_code,
                'DISCOUNT_VALUE' => $request->discount_value,
                'MIN_ORDER_TOTAL' => $request->min_order_total,
                'START_DATE' => $request->start_date,
                'EXPIRATION_DATE' => $request->end_date,
                'MAX_QUANTITY' => $request->max_quantity
            ]);

            return response()->json(
                [
                    'data' => $voucher,
                    'message' => 'Cập nhật voucher thành công',
                    'statusCode' => 200,
                ]
            );
        } catch (ValidationException $e) {
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

    public function deleteVoucher(Request $request, $id)
    {
        $voucher = Voucher::where('VOUCHER_ID', $id)->where('IS_DELETED', 0)->first();
        if (is_null($voucher)) {
            return response()->json(
                [
                    'data' => null,
                    'message' => 'Không tìm thấy voucher',
                    'statusCode' => 404,
                ]
            );
        }

        $voucher->IS_DELETED = 1;
        $voucher->save();

        return response()->json(
            [
                'data' => $voucher,
                'message' => 'Xoá Voucher thành công',
                'statusCode' => 200,
            ]
        );
    }
}
