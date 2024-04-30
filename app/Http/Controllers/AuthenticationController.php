<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Verified;
use App\Models\User;

use Illuminate\Http\Request;

class AuthenticationController extends Controller
{
    public function signup(Request $request) {
        $user =  User::create([
            'name' => $request->fullname,
            'email' => $request->email, 
            'phone' => $request->phoneNumber, 
            'password' => Hash::make($request->password),
            'birthday' => $request->birthday,
            'gender' => $request->gender,
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'statusCode' =>  200,
        ]);
    }

    public function signin(Request $request) {
        $email = $request->email;
        $password = $request->password;
        $user = User::where("email", $email)->first();
 

        if($user && Hash::check($password, $user->PASSWORD)){
            $token = $user->createToken($user->email."_Token")->plainTextToken;
            return response()->json([
                'statusCode' => 200,
                'token' => $token,
                'userID' => $user->USER_ID,
            ]);
        }
        
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function verify(Request $request) {
        // Tìm người dùng có ID tương ứng trong request
        $matk = $request->route('id');
        $user = User::where('USER_ID', $matk)->firstOrFail(); 
        $email = User::where('USER_ID', $matk)->value('EMAIL'); 

        // Kiểm tra xem hash truyền vào có khớp với hash của người dùng không
        if (! hash_equals((string) $request->route('hash'), sha1($email))) {
            return response()->json(['message' => 'Invalid verification link'], 400);
        }

        // Kiểm tra xem người dùng đã xác nhận email trước đó chưa
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 200);
        }
        // dd($user->markEmailAsVerified(), $user->hasVerifiedEmail(), $user, $matk);

        // Xác nhận email cho người dùng và gửi event Verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['message' => 'Email verified'], 200);
    }
}
