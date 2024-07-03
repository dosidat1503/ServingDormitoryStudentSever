<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Verified;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use stdClass;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AuthenticationController extends Controller
{
    public function signup(Request $request) {
        $Validator = Validator::make($request->all(), [ 
            'email' => 'required|email:191|unique:user', 
        ]);

        if($Validator->fails()){
            return response()->json([
                'statusCode' =>  422,
            ]);
        } 
        
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
 
    public function signin(Request $request){   
        $email = $request->email;
        $password = $request->password;
        $taikhoan = User::where('email', $request->email)->first();

        $validator = Validator::make($request->all(), [
            'email' => 'required|email:191',
            'password' => 'required',
        ]);
        
        if($validator->fails())
        {
            return response()->json([
                'statusCode' => 1,// không đúng định dạng
                'message' => "Mật khẩu hoặc email không đúng", 
            ]);
        }
        else
        {
            if(!$taikhoan) {  
                return response()->json([
                    'statusCode' => 1,
                    'message' => "Email không tồn tại",
                ]);  
            }
            else if($taikhoan && !Hash::check($request->password,$taikhoan->PASSWORD)){ 
                return response()->json([
                    'statusCode'=>3,
                    'message' => "Mật khẩu sai",
                ]);
            }
            else { 
                if (is_null($taikhoan->email_verified_at)) {  
                    return response()->json([
                        'statusCode'=>4,
                        'message' => "Tài khoản chưa được xác nhận",
                    ]);
                }   
                $token = $taikhoan->createToken($taikhoan->email.'_Token')->plainTextToken;


                $infoUserAtHome =  DB::table("user")
                ->join("image", "image.IMAGE_ID", "=", "user.AVT_IMAGE_ID")
                ->where("user.USER_ID", $taikhoan->USER_ID)
                ->select("user.NAME", "image.URL as AVT_URL")
                ->first();
                    
                return response()->json([
                    'statusCode' => 200,
                    'token' => $token,
                    'userID' => $taikhoan->USER_ID,
                    'infoUserAtHome' => $infoUserAtHome
                ]);
                // }
            } 
        } 
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

    public function sendMailRecoverPassword(Request $request) {
        $email = $request->email;
        $taikhoan = User::where('email', $request->email)->first();
        if (!$taikhoan) { 
            $data = new stdClass();
            $data->email = "Email không tồn tại"; 
            return response()->json([
                'statusCode'=> 1,
                'message' => "Email không tồn tại",
            ]);  
        }
        
        $password = Str::random(10);
        $password_hash = Hash::make($password);
        DB::update("UPDATE user SET PASSWORD = '$password_hash' Where EMAIL = '$email'");
        Mail::send('mailRecoverPassword', ['password' => $password], function($message) use ($email){
            $message->to($email);
        });
        return response()->json([
            'statusCode'=>200,  
        ]);  
    }

    public function logout() { 
        Auth::user()->tokens()->delete();
        return response()->json([
            'statusCode'=>200,
            'message'=>'Logged out Successfully',
        ]);
    }
}
