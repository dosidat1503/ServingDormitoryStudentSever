<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail; // Add this line
use Illuminate\Support\Str;

class AccountManagementController extends Controller
{
    public function getInfoAccount(Request $request){
        $user = $request->userID;

        $accountInfo = DB::table('USER')
                        ->join('IMAGE', 'USER.AVT_IMAGE_ID', '=', 'IMAGE.IMAGE_ID')
                        ->select( 'NAME', 'EMAIL', 'PHONE', 'BIRTHDAY', 'LINK_FB', 'SCHOOL', 'ADDRESS', 'URL')
                        ->get();

        return response()->json([
            'statusCode' => 200,
            'accountInfo' => $accountInfo[0],
        ]);
    }

    public function verifyChangeMail(Request $request){
        $userID = $request->userID; 
        $email = $request->email;

        $code = Str::random(5); 
        DB::update("UPDATE user SET CODEVERIFYCHANGEMAIL = '$code' Where USER_ID = '$userID'");
        Mail::send('mailSendCodeToChangeMail', ['code' => $code], function($message) use ($email){
            $message->to($email);
        });

        DB::table('USER')
            ->where('USER_ID', $userID)
            ->update([
                'CODEVERIFYCHANGEMAIL' => $code,
            ]);
        
        return response()->json([
            'statusCode' => 200,
        ]);
    }

    public function updateAccountInfo(Request $request){ 
        $userID = $request->userID;
        $name = $request->name;
        $phoneNumber = $request->phoneNumber;
        $avatar = $request->avatar;
        $email = $request->email;
        $birthDate = $request->birthDate;
        $facebookLink = $request->facebookLink;
        $schoolName = $request->schoolName;
        $address = $request->address;
        $avatarURLBeforeChange = $request->avatarURLBeforeChange;
        $codeVerifyEmail = $request->codeVerifyEmail;
        $emailBeforeChange = $request->emailBeforeChange;

        if($emailBeforeChange != $email){
            $user = DB::table('USER')
                    ->where('USER_ID', $userID)
                    ->select('CODEVERIFYCHANGEMAIL')->get();
            
            if($user[0]->CODEVERIFYCHANGEMAIL != $codeVerifyEmail){
                return response()->json([
                    'statusCode' => 1,
                    'message' => 'Mã xác nhận không đúng',
                ]);
            } 
        }
        $imageID = 0;
        $updateData = [
            'NAME' => $name,
            'PHONE' => $phoneNumber,
            'EMAIL' => $email,
            'BIRTHDAY' => $birthDate,
            'LINK_FB' => $facebookLink,
            'SCHOOL' => $schoolName,
            'ADDRESS' => $address,
            'CODEVERIFYCHANGEMAIL' => ""
        ];
        if($avatarURLBeforeChange !== $avatar){
            $imageID = DB::table('IMAGE')
                        ->insertGetId([
                            'URL' => $avatar,
                        ]);  
            $updateData['AVT_IMAGE_ID'] = $imageID;
        }
        $user = DB::table('USER')
            ->where('USER_ID', $userID)
            ->update($updateData);
        
        return response()->json([
            'statusCode' => 200,
            'message' => 'Cập nhật thông tin thành công',
        ]);
        
    }

    public function changePassword(Request $request){
        $userID = $request->userID;
        $oldPassword = $request->oldPassword;
        $newPassword = $request->newPassword;

        $user = DB::table('USER')
                ->where('USER_ID', $userID)
                ->select('PASSWORD')->get();
        
        if(!Hash::check($oldPassword, $user[0]->PASSWORD)){
            return response()->json([
                'statusCode' => 1,
                'message' => 'Mật khẩu cũ không đúng',
            ]);
        }

        DB::table('USER')
            ->where('USER_ID', $userID)
            ->update([
                'PASSWORD' => Hash::make($newPassword),
            ]);

        return response()->json([
            'statusCode' => 200,
            'message' => 'Đổi mật khẩu thành công',
        ]);
    }
}
