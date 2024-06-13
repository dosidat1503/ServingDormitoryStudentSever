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
use App\Models\Post_Image;

class HomeController extends Controller
{ 
    

    public function addPost(Request $request) {
        $topic = $request->topic;
        $content = $request->content;
        $imageList = $request->imageList;
        $userID = $request->userID;

        Post::create([
            'USER_ID' => $userID,
            'TOPIC' => $topic,
            'CONTENT' => $content, 
        ]);
        
        if(count($imageList) > 0){
            $PostSaved = Post::where("USER_ID", $userID)->orderBy('time', 'desc')->first();
            foreach($imageList as $image){
                Image::create([
                    'URL' => $image,
                    'USER_ID' => $userID,
                ]);
            }
            
            $ImageSaved = Image::where("USER_ID", $userID)->take(count($imageList))->get();
            
            foreach($ImageSaved as $image){
                Post_Image::create([
                    'POST_ID' => $PostSaved->POST_ID,
                    'IMAGE_ID' => $image->IMAGE_ID,
                ]);
            } 
        }
        return response()->json([
            'status' => "success",
            'imageList' => $imageList,
        ], 200);
    }

    public function getInfoPost(Request $request){ 
        $infoPost = Post::orderBy("TIME", 'desc')->take(20)->get();
        // $infoUser = User::all();
        $userID = $infoPost->pluck('USER_ID');
        $infoUser = User::whereIn('USER_ID', $userID)->select("NAME", "AVT_IMAGE_ID", "USER_ID")->get();
        $AvatarImageID = $infoUser->pluck('AVT_IMAGE_ID');
        $infoAvatarImage = Image::whereIn('IMAGE_ID', $AvatarImageID)->get();
        // $infoPostImage = Post_Image::whereIn('POST_ID', $infoPost->pluck('POST_ID'))->get();
        $infoPostImage = Post_Image::whereIn('POST_ID', $infoPost->pluck('POST_ID'))
        ->with('image')
        ->get()
        ->map(function ($postImage) {
            return [
                'POST_ID' => $postImage->POST_ID, 
                'URL' => $postImage->image->URL,
            ];
        });
        return response()->json([
            "statusCode" => 200,
            "infoPost" => $infoPost,
            "infoUser" => $infoUser,
            "infoAvatarImage" => $infoAvatarImage,
            'infoPostImage' => $infoPostImage
        ]);
    }

    public function searchPost(Request $request) {
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

    public function setData(Request $request) {
        //insert dữ liệu để test load dữ liệu ở quản lý đơn hàng
        {
            //insert image
            {
                //basic
                { 
                    //1
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/1_6_1702549349.png?alt=media&token=07bcfe4f-48fb-44e1-8681-1ccb27b31b59'
                    ]);
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/1_7_1702549349.png?alt=media&token=3c2b0d88-063a-47fd-901e-c9a199f2831a'
                    ]);
                    //AVT_IMAGE_ID 3
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/1_8_1702549349.jpg?alt=media&token=73c37a8a-1c2b-4cdd-a9b2-fd5120b3ca78'
                    ]);
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/1_11_1702549349.jpg?alt=media&token=d43ffbdd-4c04-4af2-bb80-b75a22023aea'
                    ]);
                    //5
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/1_12_1702549349.jpg?alt=media&token=7810d36c-6466-41a6-aa70-e9ed9b0d2c45'
                    ]);
                }

                //image shop 6 - 10
                {
                    //6
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/shop%20avarta%2Fngoquyen.jpg?alt=media&token=7ac4525e-203e-4d7d-9aff-16d30c7e29aa'
                    ]);
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/shop%20avarta%2Fbachiem.jpg?alt=media&token=f5ce98f0-3b8b-4e2c-a157-318dc5e54960'
                    ]);
                    //8
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/shop%20avarta%2Ftrasua%20az.jpg?alt=media&token=339e708b-6fa9-4637-adf1-9ff743347a43'
                    ]);
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/shop%20avarta%2Fbun%20dau%20mam%20tom%20thi%20no.jpg?alt=media&token=cf50d362-1dee-43c0-831d-c91a10767e02'
                    ]);
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/shop%20avarta%2Fcom%20ba5.png?alt=media&token=abeed245-92bf-4d96-ad97-2d1bc10f25c3'
                    ]);
                    //10 
                }

                //FAD 11 - 15
                {
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/SlideHeaderFAD_FAD%2Fcomtam.jpg?alt=media&token=b249632b-4bc6-4ce2-8057-14c54d073123'
                    ]);
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/SlideHeaderFAD_FAD%2Fpho.jpg?alt=media&token=4aed833c-79e0-477e-b5df-4bff5c9aa858'
                    ]);
                    //13
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/SlideHeaderFAD_FAD%2Ftrachanh.jpg?alt=media&token=ef8ecea0-2c84-4b53-a54c-8f16bc04bb89'
                    ]);
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/FAD%2Ftra%20sua%20thai%20xanh.jpg?alt=media&token=d7ec54f5-39a6-45b6-b528-50faf7e6821e'
                    ]);
                    //15
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/FAD%2Fbun%20thit%20nuong.jpg?alt=media&token=608dd9e7-9841-4302-aa2e-b18b9b3bcc12'
                    ]);
                }

                //post 16 - 21
                {
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/post%2Fcam%20nang%201.jpg?alt=media&token=3840906a-5bda-44f3-8c52-393789f8edb0'
                    ]);
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/post%2Fcam%20nang%202.jpg?alt=media&token=3df5c8bc-803d-4e77-bf46-506282b24a3b'
                    ]);
                    //18
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/post%2Fcam%20nang%203.jpeg?alt=media&token=4f685429-60a2-4924-91dd-0a79e2125cc2'
                    ]);
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/post%2Fao.png?alt=media&token=f6150379-368c-416b-9c0f-74c67e8f6a77'
                    ]);
                    //20
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/post%2Fchia%20khoa.jpg?alt=media&token=38d46905-b9be-47fd-955f-295e02610f15'
                    ]);
                    DB::table('image')->insert([ 
                        'URL' => 'https://firebasestorage.googleapis.com/v0/b/nt118-firebase-a9bb2.appspot.com/o/post%2Fvi.jpg?alt=media&token=14378757-c69b-4b85-8267-dba3cf8143b9'
                    ]);
                }

            }
            DB::table('user')->insert([
                'EMAIL' => '21521932@gm.uit.edu.vn',
                'PASSWORD' => '$2y$10$muWNpPd9xBFoRCLnjfdBieUuPn5SLW5IsdslelTqlo/bo7.DyJLd.',
                'PHONE' => '0968795749',
                'NAME' => 'đỗ sĩ đạt',
                'BIRTHDAY' => '2000-01-01',
                'GENDER' => 0,
                'AVT_IMAGE_ID' => 3,
                'email_verified_at' => '2024-04-18 09:53:59',
                'created_at' => \Carbon\Carbon::now()
            ]);

            //insert address
            {
                //1
                DB::table('address')->insert([
                    'DETAIL' => '123 Lương Đình Của',
                    'COMMUNE' => 'Khu phố 6',
                    'DISTRICT' => 'Phường Linh Trung',
                    'PROVINCE' => 'Thành phố thủ đức',
                    'IS_DELETED' => 0,
                    'USER_ID' => 1
                ]);

                //2
                DB::table('address')->insert([
                    'DETAIL' => 'Toà BA1, KTX Khu B', 
                    'NAME' => 'Trần Thu Thuỷ',
                    'PHONE' => '0867474888',
                    'IS_DEFAULT' => 1,
                    'IS_DELETED' => 0,
                    'USER_ID' => 1
                ]);
                DB::table('address')->insert([
                    'DETAIL' => 'Toà BA1, KTX Khu B', 
                    'NAME' => 'Nguyễn Thị Tưởng Vy',
                    'PHONE' => '0866868888',
                    'IS_DELETED' => 0,
                    'USER_ID' => 1
                ]);
            }
            //insert shop
            {
                for($i = 1; $i <= 3; $i++){
                    DB::table('shop')->insert([
                        'SHOP_NAME' => 'Cơm Ngô Quyền',
                        'PHONE' => '0968795779',
                        'AVT_IMAGE_ID' => 6,
                        'COVER_IMAGE_ID' => 6,
                        'SHOP_OWNER_ID' => 1,
                        'ADDRESS_ID' => 1,
                        'DESCRIPTION' => 'Example Description',
                        'IS_DELETED' => 0,
                        'created_at' => \Carbon\Carbon::now()
                    ]);
                    DB::table('shop')->insert([
                        'SHOP_NAME' => 'Quán cơm 3 chị em',
                        'PHONE' => '0968795779',
                        'AVT_IMAGE_ID' => 7,
                        'COVER_IMAGE_ID' => 7,
                        'SHOP_OWNER_ID' => 1,
                        'ADDRESS_ID' => 1,
                        'DESCRIPTION' => 'Quán cơm 3 chị em, cơm cho sinh viên ngon bổ rẻ',
                        'IS_DELETED' => 0,
                        'created_at' => \Carbon\Carbon::now()
                    ]);
                    DB::table('shop')->insert([
                        'SHOP_NAME' => 'Trà sữa AZ',
                        'PHONE' => '0968795779',
                        'AVT_IMAGE_ID' => 8,
                        'COVER_IMAGE_ID' => 8,
                        'SHOP_OWNER_ID' => 1,
                        'ADDRESS_ID' => 1,
                        'DESCRIPTION' => 'Example Description',
                        'IS_DELETED' => 0,
                        'created_at' => \Carbon\Carbon::now()
                    ]);
                    DB::table('shop')->insert([
                        'SHOP_NAME' => 'Bún đậu mắm tôm thị nỡ',
                        'PHONE' => '0968795779',
                        'AVT_IMAGE_ID' => 9,
                        'COVER_IMAGE_ID' => 9,
                        'SHOP_OWNER_ID' => 1,
                        'ADDRESS_ID' => 1,
                        'DESCRIPTION' => 'Example Description',
                        'IS_DELETED' => 0,
                        'created_at' => \Carbon\Carbon::now()
                    ]);
                    DB::table('shop')->insert([
                        'SHOP_NAME' => 'Quán ăn BA5',
                        'PHONE' => '0968795779',
                        'AVT_IMAGE_ID' => 10,
                        'COVER_IMAGE_ID' => 10,
                        'SHOP_OWNER_ID' => 1,
                        'ADDRESS_ID' => 1,
                        'DESCRIPTION' => 'Example Description',
                        'IS_DELETED' => 0,
                        'created_at' => \Carbon\Carbon::now()
                    ]);
                }
            }

            //insert FAD
            {
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Cơm sườn',
                    'FAD_PRICE' => 25000,
                    'IMAGE_ID' => 11,
                    'SHOP_ID' => 1, 
                    'DESCRIPTION' => 'Cơm sườn ngô quyền',
                    'IS_DELETED' => 0
                ]); 
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Phở bò tái',
                    'FAD_PRICE' => 30000,
                    'IMAGE_ID' => 12,
                    'SHOP_ID' => 1, 
                    'DESCRIPTION' => 'Phở bò tái gia truyền, bò nhiều, nước dùng ngon',
                    'IS_DELETED' => 0
                ]);
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Trà chanh bí đao',
                    'FAD_PRICE' => 13000,
                    'IMAGE_ID' => 13,
                    'SHOP_ID' => 1, 
                    'DESCRIPTION' => 'Trà chanh',
                    'IS_DELETED' => 0
                ]);
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Trà sữa thái xanh',
                    'FAD_PRICE' => 22000,
                    'IMAGE_ID' => 14,
                    'SHOP_ID' => 1, 
                    'DESCRIPTION' => 'Trà sữa thái xanh',
                    'IS_DELETED' => 0
                ]);
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Bún thịt nướng',
                    'FAD_PRICE' => 25000,
                    'IMAGE_ID' => 15,
                    'SHOP_ID' => 1, 
                    'DESCRIPTION' => 'Bún thịt nướng',
                    'IS_DELETED' => 0
                ]);
            }

            //insert post
            {
                // post 1
                {
                    DB::table('post')->insert([
                        'USER_ID' => 1,
                        'CONTENT' => 'Cẩm nang dành cho tân sinh viên khi học đại học', 
                        'TOPIC' => 2,
                        'LIKE_QUANTITY' => 10,
                        'IS_DELETED' => 0,
                        'TIME' => '2024-05-03 15:30:00'
                    ]);
                    //image post 1
                    {
                        DB::table('post_image')->insert([
                            "POST_ID" => 1,
                            "IMAGE_ID" => 16
                        ]); 
        
                        DB::table('post_image')->insert([
                            "POST_ID" => 1,
                            "IMAGE_ID" => 17
                        ]); 
        
                        DB::table('post_image')->insert([
                            "POST_ID" => 1,
                            "IMAGE_ID" => 18
                        ]); 
                    }
                }

                // post 2
                {
                    DB::table('post')->insert([
                        'USER_ID' => 1,
                        'CONTENT' => 'Cần pass áo do mặc không vừa. Size M, màu trắng. Giá 100k. Liên hệ zalo 0968795777', 
                        'TOPIC' => 4,
                        'LIKE_QUANTITY' => 10,
                        'IS_DELETED' => 0,
                        'TIME' => '2024-05-03 15:30:00'
                    ]);
                    //image post 2
                    {
                        DB::table('post_image')->insert([
                            "POST_ID" => 2,
                            "IMAGE_ID" => 19
                        ]);  
                    }
                }

                // post 3
                {
                    DB::table('post')->insert([
                        'USER_ID' => 1,
                        'CONTENT' => 'Khi đi học về mình có đánh rơi ví và chìa khoá ở ngã tư quốc phòng. Ai nhặt được liên hệ qua zalo 0968795777. Mình xin cảm ơn', 
                        'TOPIC' => 3,
                        'LIKE_QUANTITY' => 10,
                        'IS_DELETED' => 0,
                        'TIME' => '2024-05-03 15:30:00'
                    ]);
                    //image post 2
                    {
                        DB::table('post_image')->insert([
                            "POST_ID" => 3,
                            "IMAGE_ID" => 20
                        ]);  
                        DB::table('post_image')->insert([
                            "POST_ID" => 3,
                            "IMAGE_ID" => 21
                        ]);  
                    }
                }
    
            }

            //insert order 
            for($i = 1; $i <= 5; $i++) { 
                for($j = 1; $j <= 20; $j++) {
                    DB::table('order')->insert([ 
                        'USER_ID' => 1,
                        'ORDER_ADDRESS_ID' => 1, 
                        'PAYMENT_METHOD' => "Tiền mặt",
                        'VOUCHER_ID' => 1,
                        'STATUS' => $i,
                        'TOTAL_PAYMENT' => 21.98,
                        'DATE' => \Carbon\Carbon::now()
                    ]); 
                    DB::table('order_detail')->insert([
                        'ORDER_ID' => $j + ( $i - 1 ) * 20 ,
                        'FAD_ID' => 1,
                        'QUANTITY' => 2,
                        'PRICE' => 10.99, 
                    ]);
                } 
            }

        } 
    }

}
