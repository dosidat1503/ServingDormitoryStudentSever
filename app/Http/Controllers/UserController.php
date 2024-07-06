<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

// bang bui & le xuan

class UserController extends Controller
{
    public function getUserDetails(Request $request, $id)
    {
        $user = User::where('USER_ID', $id)->first();

        if (!$user) {
            return response()->json([
                'statusCode' => 404,
                'message' => 'Không tìm thấy người dùng',
            ], 404);
        }

        return response()->json([
            'data' => $user,
            'message' => 'Lấy thông tin người dùng thành công',
            'statusCode' => 200,
        ]);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::where('USER_ID', $id)->first();

        if (!$user) {
            return response()->json([
                'statusCode' => 404,
                'message' => 'Không tìm thấy thông tin người dùng',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'password' => 'sometimes|required|string',
            'phone' => 'sometimes|required|string|max:15|unique:user,PHONE',
            'name' => 'sometimes|required|string|max:191',
            'birthday' => 'sometimes|required|date',
            'gender' => 'sometimes|required|integer',
            'avt_image_id' => 'nullable|integer|exists:image,IMAGE_ID'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 400,
                'errors' => $validator->errors(),
            ], 400);
        }

        $user->PASSWORD = Hash::make($request['password']);
        $user->PHONE = $request['phone'];
        $user->NAME = $request['name'];
        $user->BIRTHDAY = $request['birthday'];
        $user->GENDER = $request['gender'];
        $user->AVT_IMAGE_ID = $request['avt_image_id'];
        $user->save();

        return response()->json([
            'statusCode' => 200,
            'message' => 'Cập nhật thông tin user thành công',
            'data' => $user,
        ]);
    }
}
