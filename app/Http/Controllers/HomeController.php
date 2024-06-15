<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

use App\Models\Address;
use App\Models\Image;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use App\Models\Post;

class HomeController extends Controller
{


    public function addPost(Request $request)
    {
        $topic = $request->topic;
        $content = $request->content;
        $imageID = $request->imageID;
        $userID = $request->userID;

        Post::create([
            'USER_ID' => $userID,
            'TOPIC' => $topic,
            'CONTENT' => $content,
            'IMAGE_ID' => $imageID,

        ]);

        return response()->json(['statusCode' => 200], 200);
    }

    public function getInfoPost(Request $request)
    {
        $infoPost = Post::orderBy("TIME", 'desc')->take(20)->get();
        // $infoUser = User::all();
        $userID = $infoPost->pluck('USER_ID');
        $infoUser = User::whereIn('USER_ID', $userID)->select("NAME", "AVT_IMAGE_ID", "USER_ID")->get();
        $AvatarImageID = $infoUser->pluck('AVT_IMAGE_ID');
        $infoAvatarImage = Image::whereIn('IMAGE_ID', $AvatarImageID)->get();

        return response()->json([
            "statusCode" => 200,
            "infoPost" => $infoPost,
            "infoUser" => $infoUser,
            "infoAvatarImage" => $infoAvatarImage,
        ]);
    }

    public function searchPost(Request $request)
    {
        $textQueryPost = $request->textQueryPost;

        $infoPost = Post::where("CONTENT", "LIKE", "%$textQueryPost%")->orderBy("TIME", 'desc')->get();

        $userID = $infoPost->pluck('USER_ID');
        $infoUser = User::whereIn('USER_ID', $userID)->select("NAME", "AVT_IMAGE_ID", "USER_ID")->get();
        $AvatarImageID = $infoUser->pluck('AVT_IMAGE_ID');
        $infoAvatarImage = Image::whereIn('IMAGE_ID', $AvatarImageID)->get();

        return response()->json([
            'statusCode' => 200,
            'infoPost' => $infoPost,
            'infoUser' => $infoUser,
            'infoAvatarImage' => $infoAvatarImage,
        ], 200);
    }

    public function setData(Request $request)
    {
        //insert dữ liệu để test load dữ liệu ở quản lý đơn hàng
        {
            DB::table('image')->insert([
                'IMAGE_ID' => '1_6_1702549349',
                'URL' => 'http://localhost:8000/images/products/1/1_6_1702549349.png'
            ]);
            DB::table('user')->insert([
                'EMAIL' => '21521932@gm.uit.edu.vn',
                'PASSWORD' => '$2y$10$muWNpPd9xBFoRCLnjfdBieUuPn5SLW5IsdslelTqlo/bo7.DyJLd.',
                'PHONE' => '0968795749',
                'NAME' => 'đỗ sĩ đạt',
                'BIRTHDAY' => '2000-01-01',
                'GENDER' => 0,
                'AVT_IMAGE_ID' => null,
                'email_verified_at' => '2024-04-18 09:53:59',
                'created_at' => \Carbon\Carbon::now()
            ]);
            DB::table('address')->insert([
                'DETAIL' => '123 Example Street',
                'COMMUNE' => 'Example Commune',
                'DISTRICT' => 'Example District',
                'PROVINCE' => 'Example Province',
                'IS_DELETED' => 0,
                'USER_ID' => 1
            ]);
            //insert shop
            {
                for ($i = 1; $i <= 3; $i++) {
                    DB::table('shop')->insert([
                        'SHOP_NAME' => 'Cơm Ngô Quyền',
                        'PHONE' => '0968795779',
                        'AVT_IMAGE_ID' => '1_6_1702549349',
                        'COVER_IMAGE_ID' => '1_6_1702549349',
                        'SHOP_OWNER_ID' => 1,
                        'ADDRESS_ID' => 1,
                        'DESCRIPTION' => 'Example Description',
                        'IS_DELETED' => 0,
                        'created_at' => \Carbon\Carbon::now()
                    ]);
                    DB::table('shop')->insert([
                        'SHOP_NAME' => 'Quán cơm 3 chị em',
                        'PHONE' => '0968795779',
                        'AVT_IMAGE_ID' => '1_6_1702549349',
                        'COVER_IMAGE_ID' => '1_6_1702549349',
                        'SHOP_OWNER_ID' => 1,
                        'ADDRESS_ID' => 1,
                        'DESCRIPTION' => 'Example Description',
                        'IS_DELETED' => 0,
                        'created_at' => \Carbon\Carbon::now()
                    ]);
                    DB::table('shop')->insert([
                        'SHOP_NAME' => 'Trà sữa AZ',
                        'PHONE' => '0968795779',
                        'AVT_IMAGE_ID' => '1_6_1702549349',
                        'COVER_IMAGE_ID' => '1_6_1702549349',
                        'SHOP_OWNER_ID' => 1,
                        'ADDRESS_ID' => 1,
                        'DESCRIPTION' => 'Example Description',
                        'IS_DELETED' => 0,
                        'created_at' => \Carbon\Carbon::now()
                    ]);
                    DB::table('shop')->insert([
                        'SHOP_NAME' => 'Bún đậu mắm tôm thị nỡ',
                        'PHONE' => '0968795779',
                        'AVT_IMAGE_ID' => '1_6_1702549349',
                        'COVER_IMAGE_ID' => '1_6_1702549349',
                        'SHOP_OWNER_ID' => 1,
                        'ADDRESS_ID' => 1,
                        'DESCRIPTION' => 'Example Description',
                        'IS_DELETED' => 0,
                        'created_at' => \Carbon\Carbon::now()
                    ]);
                    DB::table('shop')->insert([
                        'SHOP_NAME' => 'Quán ăn BA5',
                        'PHONE' => '0968795779',
                        'AVT_IMAGE_ID' => '1_6_1702549349',
                        'COVER_IMAGE_ID' => '1_6_1702549349',
                        'SHOP_OWNER_ID' => 1,
                        'ADDRESS_ID' => 1,
                        'DESCRIPTION' => 'Example Description',
                        'IS_DELETED' => 0,
                        'created_at' => \Carbon\Carbon::now()
                    ]);
                }
            }
            // insert food
            DB::table('fad')->insert([
                'FAD_NAME' => 'Phở bò',
                'FAD_TYPE' => 'FOOD',
                'FAD_PRICE' => 10.99,
                'IMAGE_ID' => '1_6_1702549349',
                'SHOP_ID' => 1,
                'ID_PARENTFADOFTOPPING' => null,
                'DESCRIPTION' => 'Example Description',
                'IS_DELETED' => 0
            ]);

            DB::table('fad')->insert([
                'FAD_NAME' => 'Trà sữa',
                'FAD_TYPE' => 'DRINK',
                'FAD_PRICE' => 5.99,
                'IMAGE_ID' => '1_6_1702549349',
                'SHOP_ID' => 1,
                'ID_PARENTFADOFTOPPING' => null,
                'DESCRIPTION' => 'Example Description',
                'IS_DELETED' => 0
            ]);

            DB::table('fad')->insert([
                'FAD_NAME' => 'Thịt bò',
                'FAD_TYPE' => 'TOPPING',
                'FAD_PRICE' => 0.5,
                'IMAGE_ID' => '1_6_1702549349',
                'SHOP_ID' => 1,
                'ID_PARENTFADOFTOPPING' => 1,
                'DESCRIPTION' => 'Example Description',
                'IS_DELETED' => 0
            ]);

            DB::table('fad')->insert([
                'FAD_NAME' => 'Trân châu',
                'FAD_TYPE' => 'TOPPING',
                'FAD_PRICE' => 0.5,
                'IMAGE_ID' => '1_6_1702549349',
                'SHOP_ID' => 1,
                'ID_PARENTFADOFTOPPING' => 2,
                'DESCRIPTION' => 'Example Description',
                'IS_DELETED' => 0
            ]);

            for ($i = 1; $i <= 5; $i++) {
                for ($j = 1; $j <= 20; $j++) {
                    DB::table('order')->insert([
                        'SHOP_ID' => 1,
                        'USER_ID' => 1,
                        'ORDER_ADDRESS_ID' => 1,
                        'PAYMENT_METHOD' => "Tiền mặt",
                        'VOUCHER_ID' => 1,
                        'STATUS' => $i,
                        'TOTAL_PAYMENT' => 21.98,
                        'DATE' => \Carbon\Carbon::now()
                    ]);
                    DB::table('order_detail')->insert([
                        'ORDER_ID' => $j + ($i - 1) * 20,
                        'FAD_ID' => 1,
                        'QUANTITY' => 2,
                        'PRICE' => 10.99,
                    ]);
                }
            }
        }
    }
}
