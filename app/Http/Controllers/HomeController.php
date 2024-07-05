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


    public function addPost(Request $request){
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
        $textQueryPost = $request->input('textQueryPost', '');
        $topics = $request->input('topic', []); // Lấy các giá trị của topic, mặc định là một mảng rỗng
        $startDate = $request->input('startDate', null);
        $endDate = $request->input('endDate', null);
        $sortBy = $request->input('sortBy', 1);
        $itemQuantityEveryLoad = $request->input('itemQuantityEveryLoad');
        $startIndex = $request->input('startIndex');
        $isLikeAndSave = $request->input('isLikeAndSave');
        $isManagePost = $request->input('isManagePost');
        $userID = $request->input('userID');

        $infoPost = []; 

        $infoPostQuery = Post::query();  
        

        if (!empty($textQueryPost)) {
            $infoPostQuery->where('CONTENT', 'LIKE', '%' . $textQueryPost . '%');
        }
        
        if (!empty($topics)) {
            $infoPostQuery->whereIn('TOPIC', $topics);
        }
        
        if (!is_null($startDate) && $endDate == "") {
            $infoPostQuery->where('TIME', '=', $startDate);
        }
        else{
            // Thêm điều kiện cho ngày tháng nếu tồn tại
            if (!is_null($startDate)) {
                $infoPostQuery->where('TIME', '>=', $startDate);
            }
    
            if (!is_null($endDate)) {
                $infoPostQuery->where('TIME', '<=', $endDate);
            }
        }

        //nếu sortBy = 1 thì sắp xếp theo thời gian, 2 là sắp xếp theo số lượng like
        if ($sortBy == 2) {
            $infoPostQuery->orderBy('LIKE_QUANTITY', 'desc');
        } else {
            $infoPostQuery->orderBy('TIME', 'desc');
        }

        //cái này dùng để lấy ra những bài viết mà user đã like hoặc save ( khi ở trong trang quản lý bài viết đã thích và lưu thì mới truyền giá trị cho $isLikeAndSave )
        if($isLikeAndSave == true){
            // $postIDLikeAndSave = DB::table('post_interaction')
            //                 ->where('USER_ID', $userID)
            //                 ->where('IS_LIKE', 1)
            //                 ->orWhere('IS_SAVE', 1)
            //                 ->pluck('POST_ID');

            // $infoPostQuery->whereIn('POST_ID', $postIDLikeAndSave);
            $infoPostQuery = $infoPostQuery->join('post_interaction', 'post.POST_ID', '=', 'post_interaction.POST_ID')
                    ->where('post_interaction.USER_ID', $userID)
                    ->where(function($query) {
                        $query->where('post_interaction.IS_LIKE', 1)
                              ->orWhere('post_interaction.IS_SAVE', 1);
                    })
                    ->select('post.*', 'post_interaction.IS_LIKE', 'post_interaction.IS_SAVE');
        }
        else{
            // lọc ra những bài viết mà mình đã like và save để hiển thị ở trang chủ, hoặc khi tìm kiếm
            $infoPostQuery = $infoPostQuery->leftJoin('post_interaction', function($join) use ($userID) {
                                $join->on('post.POST_ID', '=', 'post_interaction.POST_ID')
                                    ->where('post_interaction.USER_ID', '=', $userID);
                            })
                            ->select('post.*','post_interaction.IS_LIKE', 'post_interaction.IS_SAVE')
                            ->where(function($query) {
                                $query->where('post_interaction.IS_LIKE', 1)
                                    ->orWhere('post_interaction.IS_SAVE', 1)
                                    ->orWhereNull('post_interaction.POST_ID'); // Điều kiện này đảm bảo rằng cả những bài viết không có trong bảng post_interaction cũng được lấy ra
                            });
        }

        if($isManagePost == true){
            $infoPostQuery = $infoPostQuery->where('post.USER_ID', $userID);
        }

        // Thực hiện truy vấn với sắp xếp và giới hạn số lượng kết quả
        $infoPost = $infoPostQuery->skip($startIndex)->take($itemQuantityEveryLoad)->get(); 
 
        $userID = $infoPost->pluck('USER_ID'); 
        
        $usersInfo = DB::table('user')
                        ->join('image', "user.AVT_IMAGE_ID", "=", "image.IMAGE_ID")
                        ->whereIn('user.USER_ID', $userID)
                        ->select('user.NAME', 'user.USER_ID', 'image.URL AS AVT_URL')
                        ->get();
        
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
            "usersInfo" => $usersInfo, 
            'infoPostImage' => $infoPostImage, 
        ]);
    }

    public function searchPost(Request $request) {
        $textQueryPost = $request->textQueryPost;  
        $infoPost = Post::where("CONTENT", "LIKE", "%$textQueryPost%")->orderBy("TIME", 'desc')->take(20)->get();
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

    public function interactPost(Request $request){
        $userID = $request->userID;
        $postID = $request->postID;
        $type = $request->type; 
        $yesOrNo = $request->yesOrNo;

        $post = Post::where('POST_ID', $postID)->get(); 
        $likeQuantityUpdate = $post[0]->LIKE_QUANTITY;
                
        $existLike = DB::table('post_interaction')
                ->where('USER_ID', $userID)
                ->where('POST_ID', $postID)
                ->where('IS_LIKE', 1)
                ->get();  

        $existSave = DB::table('post_interaction')
                ->where('USER_ID', $userID)
                ->where('POST_ID', $postID)
                ->where('IS_SAVE', 1)
                ->get();
        if($type == "isLiked"){
            if($yesOrNo == 1){
                // = 1 thì chưa có like là hiển nhiên bởi vì ở trên frontEnd nếu mà đã like thì nó sẽ cho isLike = true, mà true nhấn vào thì yesOrNo = 0
                if($existSave->isEmpty()){//nếu mà đã có save do user tương tác trên bài viết này thì cập nhật thêm giá trị cho thuộc tính like
                    DB::table("post_interaction")->insert([
                        'USER_ID' => $userID,
                        'POST_ID' => $postID,
                        'IS_LIKE' => 1,
                    ]);
                }
                else{//nếu mà chưa có like do user tương tác trên bài viết này thì insert. Nếu đã có rồi thì ko làm gì nữa 
                    DB::table('post_interaction')
                        ->where('USER_ID', $userID)
                        ->where('POST_ID', $postID)
                        ->update(['IS_LIKE' => 1]);
                } 
                $likeQuantityUpdate++;
            }
            else{
                if($existSave->isEmpty()){ 
                    DB::table("post_interaction")
                    ->where('USER_ID', $userID)
                    ->where('POST_ID', $postID)
                    ->delete();
                }
                else{
                    DB::table('post_interaction')
                    ->where('USER_ID', $userID)
                    ->where('POST_ID', $postID)
                    ->update([
                        'IS_LIKE' => null,
                    ]); 
                } 
                $likeQuantityUpdate--;
            }
            DB::table('post')
                ->where('POST_ID', $postID)
                ->update([
                    'LIKE_QUANTITY' => $likeQuantityUpdate,
                ]);
        }
        else if($type == "isSaved"){
            if($yesOrNo == 1){ 
                if($existLike->isEmpty()){  
                    DB::table("post_interaction")->insert([
                        'USER_ID' => $userID,
                        'POST_ID' => $postID,
                        'IS_SAVE' => 1,
                    ]);
                }
                else{
                    DB::table('post_interaction')
                        ->where('USER_ID', $userID)
                        ->where('POST_ID', $postID)
                        ->update(['IS_SAVE' => 1]);
                }
            }
            else{ 
                if($existLike->isEmpty()){ 
                    DB::table("post_interaction")
                    ->where('USER_ID', $userID)
                    ->where('POST_ID', $postID)
                    ->delete();
                }
                else{
                    DB::table('post_interaction')
                        ->where('USER_ID', $userID)
                        ->where('POST_ID', $postID)
                        ->update([ 'IS_SAVE' => null ]);
                }
            }
        } 
        return response()->json([
            'statusCode' => 200,
        ]);
    }

    
    public function editPost(Request $request){
        $userID = $request->userID;
        $postID = $request->postID;
        $content = $request->content;

        DB::table("post")
        ->where('POST_ID', $postID)
        ->update(['CONTENT' => $content]);

        return response()->json([
            'statusCode' => 200,
            'message' => 'Post has been updated',
        ]);
    }

    public function deletePost(Request $request){ 
        $postID = $request->postID;
        // delete relate info in post_image post_interact image
        DB::table("post_image")
        ->join('image', 'post_image.IMAGE_ID', '=', 'image.IMAGE_ID')
        ->where('POST_ID', $postID)
        ->delete();

        DB::table("post_interaction")
        ->where('POST_ID', $postID)
        ->delete();

        DB::table("post")
        ->where('POST_ID', $postID)
        ->delete();

        return response()->json([
            'statusCode' => 200,
            'message' => 'Post has been deleted',
        ]);
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
                'PHONE' => '0968795741',
                'NAME' => 'Nguyễn Văn Tấn',
                'BIRTHDAY' => '2000-01-01',
                'GENDER' => 0,
                'LINK_FB' => "https://www.facebook.com/NguyenVanTan",
                'SCHOOL' => "Đại học công nghệ thông tin - ĐHQG TP.HCM",
                'ADDRESS' => "KTX Khu B, ĐHQG TP.HCM",
                'AVT_IMAGE_ID' => 1,
                'email_verified_at' => '2024-04-18 09:53:59',
                'created_at' => \Carbon\Carbon::now()
            ]);
            DB::table('user')->insert([
                'EMAIL' => 'dosidat@gmail.com',
                'PASSWORD' => '$2y$10$muWNpPd9xBFoRCLnjfdBieUuPn5SLW5IsdslelTqlo/bo7.DyJLd.',
                'PHONE' => '0968795742',
                'NAME' => 'Trần Trung Hiếu',
                'BIRTHDAY' => '2000-01-02',
                'GENDER' => 0,
                'LINK_FB' => "https://www.facebook.com/TranTrungHieu",
                'SCHOOL' => "Đại học công nghệ thông tin - ĐHQG TP.HCM",
                'ADDRESS' => "KTX Khu A, ĐHQG TP.HCM",
                'AVT_IMAGE_ID' => 2,
                'email_verified_at' => '2024-04-18 09:53:59',
                'created_at' => \Carbon\Carbon::now()
            ]);
            DB::table('user')->insert([
                'EMAIL' => 'hkc99391@gmail.com',
                'PASSWORD' => '$2y$10$muWNpPd9xBFoRCLnjfdBieUuPn5SLW5IsdslelTqlo/bo7.DyJLd.',
                'PHONE' => '0968795743',
                'NAME' => 'Trần Hoàng Quân',
                'BIRTHDAY' => '2000-01-03',
                'GENDER' => 1,
                'LINK_FB' => "https://www.facebook.com/TranHoangQuan",
                'SCHOOL' => "Đại học công nghệ thông tin - ĐHQG TP.HCM",
                'ADDRESS' => "KTX Khu A, ĐHQG TP.HCM",
                'AVT_IMAGE_ID' => 3,
                'email_verified_at' => '2024-04-18 09:53:59',
                'created_at' => \Carbon\Carbon::now()
            ]);

            //insert address
            {
                //1
                DB::table('address')->insert([
                    'NAME' => 'Đỗ Phạm Hoàng Ân',
                    'PHONE' => '0866868888',
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
                for ($i = 1; $i <= 3; $i++) {
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
                    'IS_DELETED' => 0,
                    'CATEGORY' => 1,
                    'TAG' => 1, 
                    'DATE' => now()
                ]); 
                //size of FAD_ID 1
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Bì',
                    'FAD_PRICE' => 5000,
                    'IMAGE_ID' => 11,
                    'SHOP_ID' => 1, 
                    'ID_PARENTFADOFTOPPING' => 1, 
                    'IS_DELETED' => 0, 
                    'DATE' => now()
                ]);
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Chả',
                    'FAD_PRICE' => 5000,
                    'IMAGE_ID' => 11,
                    'SHOP_ID' => 1, 
                    'ID_PARENTFADOFTOPPING' => 1, 
                    'IS_DELETED' => 0, 
                    'DATE' => now()
                ]); 
                //4
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Phở bò tái',
                    'FAD_PRICE' => 30000,
                    'IMAGE_ID' => 12,
                    'SHOP_ID' => 5, 
                    'DESCRIPTION' => 'Phở bò tái gia truyền, bò nhiều, nước dùng ngon',
                    'IS_DELETED' => 0,
                    'CATEGORY' => 1,
                    'TAG' => 2, 
                    'DATE' => now()
                ]);
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Trà chanh bí đao',
                    'FAD_PRICE' => 13000,
                    'IMAGE_ID' => 13,
                    'SHOP_ID' => 3, 
                    'DESCRIPTION' => 'Trà chanh',
                    'IS_DELETED' => 0,
                    'CATEGORY' => 2,
                    'TAG' => 5, 
                    'DATE' => now()
                ]);
                //6
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Trà sữa thái xanh',
                    'FAD_PRICE' => 22000,
                    'IMAGE_ID' => 14,
                    'SHOP_ID' => 3, 
                    'DESCRIPTION' => 'Trà sữa thái xanh',
                    'IS_DELETED' => 0,
                    'CATEGORY' => 2,
                    'TAG' => 4, 
                    'DATE' => now()
                ]);
                //size of FAD_ID 6
                DB::table('fad')->insert([
                    'FAD_NAME' => 'M',
                    'FAD_PRICE' => 0,
                    'IMAGE_ID' => 14,
                    'SHOP_ID' => 3, 
                    'DESCRIPTION' => 'Trà sữa thái xanh',
                    'ID_PARENTFADOFSIZE' => 6,
                    'IS_DELETED' => 0, 
                    'DATE' => now()
                ]);
                DB::table('fad')->insert([
                    'FAD_NAME' => 'L',
                    'FAD_PRICE' => 4000,
                    'IMAGE_ID' => 14,
                    'SHOP_ID' => 3, 
                    'DESCRIPTION' => 'Trà sữa thái xanh',
                    'ID_PARENTFADOFSIZE' => 6,
                    'IS_DELETED' => 0, 
                    'DATE' => now()
                ]);
                //topping of FAD_ID 6
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Trân châu đen',
                    'FAD_PRICE' => 5000,
                    'IMAGE_ID' => 14,
                    'SHOP_ID' => 3, 
                    'DESCRIPTION' => 'Trà sữa thái xanh',
                    'ID_PARENTFADOFTOPPING' => 6,
                    'IS_DELETED' => 0, 
                    'DATE' => now()
                ]);
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Viên phô mai',
                    'FAD_PRICE' => 5000,
                    'IMAGE_ID' => 14,
                    'SHOP_ID' => 3, 
                    'DESCRIPTION' => 'Trà sữa thái xanh',
                    'ID_PARENTFADOFTOPPING' => 6,
                    'IS_DELETED' => 0, 
                    'DATE' => now()
                ]);
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Bún thịt nướng',
                    'FAD_PRICE' => 25000,
                    'IMAGE_ID' => 15,
                    'SHOP_ID' => 5, 
                    'DESCRIPTION' => 'Bún thịt nướng',
                    'IS_DELETED' => 0,
                    'CATEGORY' => 1,
                    'TAG' => 2, 
                    'DATE' => now()
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
                        'LIKE_QUANTITY' => 20,
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
                        'TIME' => '2024-05-19 15:30:00'
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
                        'LIKE_QUANTITY' => 30,
                        'IS_DELETED' => 0,
                        'TIME' => '2024-04-03 15:30:00'
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

                
                // post 4
                {
                    DB::table('post')->insert([
                        'USER_ID' => 2,
                        'CONTENT' => 'Bài viết có ID = 4 ', 
                        'TOPIC' => 1,
                        'LIKE_QUANTITY' => 40,
                        'IS_DELETED' => 0,
                        'TIME' => '2024-05-28 15:30:00'
                    ]);
                    //image post 2
                    {
                        DB::table('post_image')->insert([
                            "POST_ID" => 4,
                            "IMAGE_ID" => 20
                        ]);   
                    }
                }

                // post 5
                {
                    DB::table('post')->insert([
                        'USER_ID' => 2,
                        'CONTENT' => 'Bài viết có ID = 5', 
                        'TOPIC' => 5,
                        'LIKE_QUANTITY' => 50,
                        'IS_DELETED' => 0,
                        'TIME' => '2024-05-22 15:30:00'
                    ]);
                    //image post 2
                    {
                        DB::table('post_image')->insert([
                            "POST_ID" => 5,
                            "IMAGE_ID" => 20
                        ]);   
                    }
                }

                // post 6
                {
                    DB::table('post')->insert([
                        'USER_ID' => 3,
                        'CONTENT' => 'Bài viết có ID = 6', 
                        'TOPIC' => 5,
                        'LIKE_QUANTITY' => 60,
                        'IS_DELETED' => 0,
                        'TIME' => '2024-05-01 15:30:00'
                    ]);
                    //image post 2
                    {
                        DB::table('post_image')->insert([
                            "POST_ID" => 6,
                            "IMAGE_ID" => 20
                        ]);   
                    }
                }

                // post 7
                {
                    DB::table('post')->insert([
                        'USER_ID' => 3,
                        'CONTENT' => 'Bài viết có ID = 7', 
                        'TOPIC' => 1,
                        'LIKE_QUANTITY' => 70,
                        'IS_DELETED' => 0,
                        'TIME' => '2024-05-12 15:30:00'
                    ]);
                    //image post 2
                    {
                        DB::table('post_image')->insert([
                            "POST_ID" => 6,
                            "IMAGE_ID" => 19
                        ]);   
                    }
                }
    
            }
            // DB::table("VOUCHER")
            // ->insert([
            //     'VOUCHER_CODE' => 'VC_CTNQ_T1',
            //     'DISCOUNT_VALUE' => 10000, 
            //     'MAX_QUANTITY' => 100,
            //     'VOUCHER_CODE' => "CT_NGQ_T1",
            //     'SHOP_ID' => 1, 
            //     'START_DATE' => '2024-05-01',
            //     'EXPIRATION_DATE' => '2024-05-31',
            //     'REMAIN_AMOUNT' => 50
            // ]);

            
            //insert voucher
            {
                DB::table('voucher')->insert([
                    'VOUCHER_CODE' => 'CT_NGQ_T7',
                    'DISCOUNT_VALUE' => 10, 
                    'MAX_QUANTITY' => 100,
                    'SHOP_ID' => 1, 
                    'MIN_ORDER_TOTAL' => 50000,
                    'START_DATE' => '2024-07-01',
                    'EXPIRATION_DATE' => '2024-07-31',
                    'REMAIN_AMOUNT' => 90
                ]);
                DB::table('voucher')->insert([
                    'VOUCHER_CODE' => 'CT_NGQ_T8',
                    'DISCOUNT_VALUE' => 20, 
                    'MAX_QUANTITY' => 100,
                    'SHOP_ID' => 1, 
                    'MIN_ORDER_TOTAL' => 30000,
                    'START_DATE' => '2024-08-01',
                    'EXPIRATION_DATE' => '2024-08-31',
                    'REMAIN_AMOUNT' => 100
                ]);
            }

            //insert order 
            for($i = 1; $i <= 5; $i++) { 
                for($j = 1; $j <= 20; $j++) {
                    DB::table('order')->insert([ 
                        'USER_ID' => 1,
                        'ADDRESS_ID' => 1,
                        'PAYMENT_METHOD' => ( $j % 4 ) == 0 ? "Tiền mặt" : "Chuyển khoản",
                        'VOUCHER_CODE' => "CT_NGQ_T7",
                        'STATUS' => $i,
                        'PAYMENT_STATUS' => ( $j % 2 ) == 0 ? 1: 0 ,
                        'TOTAL_PAYMENT' => 21.98,
                        'NOTE' => 'Giao sớm giúp em!!',
                        'DATE' => \Carbon\Carbon::now()
                        // ->addDays($i)
                    ]);
                    DB::table('order_detail')->insert([
                        'ORDER_ID' => $j + ($i - 1) * 20,
                        'FAD_ID' => $i,
                        'QUANTITY' => 2,
                        'PRICE' => 10.99,
                    ]);
                }
            }

            //insert  POST_INTERACTION
            DB::table('POST_INTERACTION')->insert([
                'POST_ID' => 4,
                'USER_ID' => 1,
                'IS_LIKE' => 1, 
            ]);

            DB::table('POST_INTERACTION')->insert([
                'POST_ID' => 5,
                'USER_ID' => 1,
                'IS_LIKE' => 1, 
            ]);

            DB::table('POST_INTERACTION')->insert([
                'POST_ID' => 6,
                'USER_ID' => 1,
                'IS_LIKE' => 1, 
                'IS_SAVE' => 1, 
            ]);

            // DB::table('POST_INTERACTION')->insert([
            //     'POST_ID' => 5,
            //     'USER_ID' => 1,
            //     'IS_SAVE' => 1, 
            // ]);

 
        } 
    }
}





\ <?php

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


    public function addPost(Request $request){
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
        $textQueryPost = $request->input('textQueryPost', '');
        $topics = $request->input('topic', []); // Lấy các giá trị của topic, mặc định là một mảng rỗng
        $startDate = $request->input('startDate', null);
        $endDate = $request->input('endDate', null);
        $sortBy = $request->input('sortBy', 1);
        $itemQuantityEveryLoad = $request->input('itemQuantityEveryLoad');
        $startIndex = $request->input('startIndex');
        $isLikeAndSave = $request->input('isLikeAndSave');
        $isManagePost = $request->input('isManagePost');
        $userID = $request->input('userID');

        $infoPost = []; 

        $infoPostQuery = Post::query();  
        

        if (!empty($textQueryPost)) {
            $infoPostQuery->where('CONTENT', 'LIKE', '%' . $textQueryPost . '%');
        }
        
        if (!empty($topics)) {
            $infoPostQuery->whereIn('TOPIC', $topics);
        }
        
        if (!is_null($startDate) && $endDate == "") {
            $infoPostQuery->where('TIME', '=', $startDate);
        }
        else{
            // Thêm điều kiện cho ngày tháng nếu tồn tại
            if (!is_null($startDate)) {
                $infoPostQuery->where('TIME', '>=', $startDate);
            }
    
            if (!is_null($endDate)) {
                $infoPostQuery->where('TIME', '<=', $endDate);
            }
        }

        //nếu sortBy = 1 thì sắp xếp theo thời gian, 2 là sắp xếp theo số lượng like
        if ($sortBy == 2) {
            $infoPostQuery->orderBy('LIKE_QUANTITY', 'desc');
        } else {
            $infoPostQuery->orderBy('TIME', 'desc');
        }

        //cái này dùng để lấy ra những bài viết mà user đã like hoặc save ( khi ở trong trang quản lý bài viết đã thích và lưu thì mới truyền giá trị cho $isLikeAndSave )
        if($isLikeAndSave == true){
            // $postIDLikeAndSave = DB::table('post_interaction')
            //                 ->where('USER_ID', $userID)
            //                 ->where('IS_LIKE', 1)
            //                 ->orWhere('IS_SAVE', 1)
            //                 ->pluck('POST_ID');

            // $infoPostQuery->whereIn('POST_ID', $postIDLikeAndSave);
            $infoPostQuery = $infoPostQuery->join('post_interaction', 'post.POST_ID', '=', 'post_interaction.POST_ID')
                    ->where('post_interaction.USER_ID', $userID)
                    ->where(function($query) {
                        $query->where('post_interaction.IS_LIKE', 1)
                              ->orWhere('post_interaction.IS_SAVE', 1);
                    })
                    ->select('post.*', 'post_interaction.IS_LIKE', 'post_interaction.IS_SAVE');
        }
        else{
            // lọc ra những bài viết mà mình đã like và save để hiển thị ở trang chủ, hoặc khi tìm kiếm
            $infoPostQuery = $infoPostQuery->leftJoin('post_interaction', function($join) use ($userID) {
                                $join->on('post.POST_ID', '=', 'post_interaction.POST_ID')
                                    ->where('post_interaction.USER_ID', '=', $userID);
                            })
                            ->select('post.*','post_interaction.IS_LIKE', 'post_interaction.IS_SAVE')
                            ->where(function($query) {
                                $query->where('post_interaction.IS_LIKE', 1)
                                    ->orWhere('post_interaction.IS_SAVE', 1)
                                    ->orWhereNull('post_interaction.POST_ID'); // Điều kiện này đảm bảo rằng cả những bài viết không có trong bảng post_interaction cũng được lấy ra
                            });
        }

        if($isManagePost == true){
            $infoPostQuery = $infoPostQuery->where('post.USER_ID', $userID);
        }

        // Thực hiện truy vấn với sắp xếp và giới hạn số lượng kết quả
        $infoPost = $infoPostQuery->skip($startIndex)->take($itemQuantityEveryLoad)->get(); 
 
        $userID = $infoPost->pluck('USER_ID'); 
        
        $usersInfo = DB::table('user')
                        ->join('image', "user.AVT_IMAGE_ID", "=", "image.IMAGE_ID")
                        ->whereIn('user.USER_ID', $userID)
                        ->select('user.NAME', 'user.USER_ID', 'image.URL AS AVT_URL')
                        ->get();
        
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
            "usersInfo" => $usersInfo, 
            'infoPostImage' => $infoPostImage, 
        ]);
    }

    public function searchPost(Request $request) {
        $textQueryPost = $request->textQueryPost;  
        $infoPost = Post::where("CONTENT", "LIKE", "%$textQueryPost%")->orderBy("TIME", 'desc')->take(20)->get();
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

    public function interactPost(Request $request){
        $userID = $request->userID;
        $postID = $request->postID;
        $type = $request->type; 
        $yesOrNo = $request->yesOrNo;

        $post = Post::where('POST_ID', $postID)->get(); 
        $likeQuantityUpdate = $post[0]->LIKE_QUANTITY;
                
        $existLike = DB::table('post_interaction')
                ->where('USER_ID', $userID)
                ->where('POST_ID', $postID)
                ->where('IS_LIKE', 1)
                ->get();  

        $existSave = DB::table('post_interaction')
                ->where('USER_ID', $userID)
                ->where('POST_ID', $postID)
                ->where('IS_SAVE', 1)
                ->get();
        if($type == "isLiked"){
            if($yesOrNo == 1){
                // = 1 thì chưa có like là hiển nhiên bởi vì ở trên frontEnd nếu mà đã like thì nó sẽ cho isLike = true, mà true nhấn vào thì yesOrNo = 0
                if($existSave->isEmpty()){//nếu mà đã có save do user tương tác trên bài viết này thì cập nhật thêm giá trị cho thuộc tính like
                    DB::table("post_interaction")->insert([
                        'USER_ID' => $userID,
                        'POST_ID' => $postID,
                        'IS_LIKE' => 1,
                    ]);
                }
                else{//nếu mà chưa có like do user tương tác trên bài viết này thì insert. Nếu đã có rồi thì ko làm gì nữa 
                    DB::table('post_interaction')
                        ->where('USER_ID', $userID)
                        ->where('POST_ID', $postID)
                        ->update(['IS_LIKE' => 1]);
                } 
                $likeQuantityUpdate++;
            }
            else{
                if($existSave->isEmpty()){ 
                    DB::table("post_interaction")
                    ->where('USER_ID', $userID)
                    ->where('POST_ID', $postID)
                    ->delete();
                }
                else{
                    DB::table('post_interaction')
                    ->where('USER_ID', $userID)
                    ->where('POST_ID', $postID)
                    ->update([
                        'IS_LIKE' => null,
                    ]); 
                } 
                $likeQuantityUpdate--;
            }
            DB::table('post')
                ->where('POST_ID', $postID)
                ->update([
                    'LIKE_QUANTITY' => $likeQuantityUpdate,
                ]);
        }
        else if($type == "isSaved"){
            if($yesOrNo == 1){ 
                if($existLike->isEmpty()){  
                    DB::table("post_interaction")->insert([
                        'USER_ID' => $userID,
                        'POST_ID' => $postID,
                        'IS_SAVE' => 1,
                    ]);
                }
                else{
                    DB::table('post_interaction')
                        ->where('USER_ID', $userID)
                        ->where('POST_ID', $postID)
                        ->update(['IS_SAVE' => 1]);
                }
            }
            else{ 
                if($existLike->isEmpty()){ 
                    DB::table("post_interaction")
                    ->where('USER_ID', $userID)
                    ->where('POST_ID', $postID)
                    ->delete();
                }
                else{
                    DB::table('post_interaction')
                        ->where('USER_ID', $userID)
                        ->where('POST_ID', $postID)
                        ->update([ 'IS_SAVE' => null ]);
                }
            }
        } 
        return response()->json([
            'statusCode' => 200,
        ]);
    }

    
    public function editPost(Request $request){
        $userID = $request->userID;
        $postID = $request->postID;
        $content = $request->content;

        DB::table("post")
        ->where('POST_ID', $postID)
        ->update(['CONTENT' => $content]);

        return response()->json([
            'statusCode' => 200,
            'message' => 'Post has been updated',
        ]);
    }

    public function deletePost(Request $request){ 
        $postID = $request->postID;
        // delete relate info in post_image post_interact image
        DB::table("post_image")
        ->join('image', 'post_image.IMAGE_ID', '=', 'image.IMAGE_ID')
        ->where('POST_ID', $postID)
        ->delete();

        DB::table("post_interaction")
        ->where('POST_ID', $postID)
        ->delete();

        DB::table("post")
        ->where('POST_ID', $postID)
        ->delete();

        return response()->json([
            'statusCode' => 200,
            'message' => 'Post has been deleted',
        ]);
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
                'PHONE' => '0968795741',
                'NAME' => 'Nguyễn Văn Tấn',
                'BIRTHDAY' => '2000-01-01',
                'GENDER' => 0,
                'LINK_FB' => "https://www.facebook.com/NguyenVanTan",
                'SCHOOL' => "Đại học công nghệ thông tin - ĐHQG TP.HCM",
                'ADDRESS' => "KTX Khu B, ĐHQG TP.HCM",
                'AVT_IMAGE_ID' => 1,
                'email_verified_at' => '2024-04-18 09:53:59',
                'created_at' => \Carbon\Carbon::now()
            ]);
            DB::table('user')->insert([
                'EMAIL' => 'dosidat@gmail.com',
                'PASSWORD' => '$2y$10$muWNpPd9xBFoRCLnjfdBieUuPn5SLW5IsdslelTqlo/bo7.DyJLd.',
                'PHONE' => '0968795742',
                'NAME' => 'Trần Trung Hiếu',
                'BIRTHDAY' => '2000-01-02',
                'GENDER' => 0,
                'LINK_FB' => "https://www.facebook.com/TranTrungHieu",
                'SCHOOL' => "Đại học công nghệ thông tin - ĐHQG TP.HCM",
                'ADDRESS' => "KTX Khu A, ĐHQG TP.HCM",
                'AVT_IMAGE_ID' => 2,
                'email_verified_at' => '2024-04-18 09:53:59',
                'created_at' => \Carbon\Carbon::now()
            ]);
            DB::table('user')->insert([
                'EMAIL' => 'hkc99391@gmail.com',
                'PASSWORD' => '$2y$10$muWNpPd9xBFoRCLnjfdBieUuPn5SLW5IsdslelTqlo/bo7.DyJLd.',
                'PHONE' => '0968795743',
                'NAME' => 'Trần Hoàng Quân',
                'BIRTHDAY' => '2000-01-03',
                'GENDER' => 1,
                'LINK_FB' => "https://www.facebook.com/TranHoangQuan",
                'SCHOOL' => "Đại học công nghệ thông tin - ĐHQG TP.HCM",
                'ADDRESS' => "KTX Khu A, ĐHQG TP.HCM",
                'AVT_IMAGE_ID' => 3,
                'email_verified_at' => '2024-04-18 09:53:59',
                'created_at' => \Carbon\Carbon::now()
            ]);

            //insert address
            {
                //1
                DB::table('address')->insert([
                    'NAME' => 'Đỗ Phạm Hoàng Ân',
                    'PHONE' => '0866868888',
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
                for ($i = 1; $i <= 3; $i++) {
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
                    'IS_DELETED' => 0,
                    'CATEGORY' => 1,
                    'TAG' => 1, 
                    'DATE' => now()
                ]); 
                //size of FAD_ID 1
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Bì',
                    'FAD_PRICE' => 5000,
                    'IMAGE_ID' => 11,
                    'SHOP_ID' => 1, 
                    'ID_PARENTFADOFTOPPING' => 1, 
                    'IS_DELETED' => 0, 
                    'DATE' => now()
                ]);
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Chả',
                    'FAD_PRICE' => 5000,
                    'IMAGE_ID' => 11,
                    'SHOP_ID' => 1, 
                    'ID_PARENTFADOFTOPPING' => 1, 
                    'IS_DELETED' => 0, 
                    'DATE' => now()
                ]); 
                //4
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Phở bò tái',
                    'FAD_PRICE' => 30000,
                    'IMAGE_ID' => 12,
                    'SHOP_ID' => 5, 
                    'DESCRIPTION' => 'Phở bò tái gia truyền, bò nhiều, nước dùng ngon',
                    'IS_DELETED' => 0,
                    'CATEGORY' => 1,
                    'TAG' => 2, 
                    'DATE' => now()
                ]);
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Trà chanh bí đao',
                    'FAD_PRICE' => 13000,
                    'IMAGE_ID' => 13,
                    'SHOP_ID' => 3, 
                    'DESCRIPTION' => 'Trà chanh',
                    'IS_DELETED' => 0,
                    'CATEGORY' => 2,
                    'TAG' => 5, 
                    'DATE' => now()
                ]);
                //6
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Trà sữa thái xanh',
                    'FAD_PRICE' => 22000,
                    'IMAGE_ID' => 14,
                    'SHOP_ID' => 3, 
                    'DESCRIPTION' => 'Trà sữa thái xanh',
                    'IS_DELETED' => 0,
                    'CATEGORY' => 2,
                    'TAG' => 4, 
                    'DATE' => now()
                ]);
                //size of FAD_ID 6
                DB::table('fad')->insert([
                    'FAD_NAME' => 'M',
                    'FAD_PRICE' => 0,
                    'IMAGE_ID' => 14,
                    'SHOP_ID' => 3, 
                    'DESCRIPTION' => 'Trà sữa thái xanh',
                    'ID_PARENTFADOFSIZE' => 6,
                    'IS_DELETED' => 0, 
                    'DATE' => now()
                ]);
                DB::table('fad')->insert([
                    'FAD_NAME' => 'L',
                    'FAD_PRICE' => 4000,
                    'IMAGE_ID' => 14,
                    'SHOP_ID' => 3, 
                    'DESCRIPTION' => 'Trà sữa thái xanh',
                    'ID_PARENTFADOFSIZE' => 6,
                    'IS_DELETED' => 0, 
                    'DATE' => now()
                ]);
                //topping of FAD_ID 6
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Trân châu đen',
                    'FAD_PRICE' => 5000,
                    'IMAGE_ID' => 14,
                    'SHOP_ID' => 3, 
                    'DESCRIPTION' => 'Trà sữa thái xanh',
                    'ID_PARENTFADOFTOPPING' => 6,
                    'IS_DELETED' => 0, 
                    'DATE' => now()
                ]);
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Viên phô mai',
                    'FAD_PRICE' => 5000,
                    'IMAGE_ID' => 14,
                    'SHOP_ID' => 3, 
                    'DESCRIPTION' => 'Trà sữa thái xanh',
                    'ID_PARENTFADOFTOPPING' => 6,
                    'IS_DELETED' => 0, 
                    'DATE' => now()
                ]);
                DB::table('fad')->insert([
                    'FAD_NAME' => 'Bún thịt nướng',
                    'FAD_PRICE' => 25000,
                    'IMAGE_ID' => 15,
                    'SHOP_ID' => 5, 
                    'DESCRIPTION' => 'Bún thịt nướng',
                    'IS_DELETED' => 0,
                    'CATEGORY' => 1,
                    'TAG' => 2, 
                    'DATE' => now()
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
                        'LIKE_QUANTITY' => 20,
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
                        'TIME' => '2024-05-19 15:30:00'
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
                        'LIKE_QUANTITY' => 30,
                        'IS_DELETED' => 0,
                        'TIME' => '2024-04-03 15:30:00'
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

                
                // post 4
                {
                    DB::table('post')->insert([
                        'USER_ID' => 2,
                        'CONTENT' => 'Bài viết có ID = 4 ', 
                        'TOPIC' => 1,
                        'LIKE_QUANTITY' => 40,
                        'IS_DELETED' => 0,
                        'TIME' => '2024-05-28 15:30:00'
                    ]);
                    //image post 2
                    {
                        DB::table('post_image')->insert([
                            "POST_ID" => 4,
                            "IMAGE_ID" => 20
                        ]);   
                    }
                }

                // post 5
                {
                    DB::table('post')->insert([
                        'USER_ID' => 2,
                        'CONTENT' => 'Bài viết có ID = 5', 
                        'TOPIC' => 5,
                        'LIKE_QUANTITY' => 50,
                        'IS_DELETED' => 0,
                        'TIME' => '2024-05-22 15:30:00'
                    ]);
                    //image post 2
                    {
                        DB::table('post_image')->insert([
                            "POST_ID" => 5,
                            "IMAGE_ID" => 20
                        ]);   
                    }
                }

                // post 6
                {
                    DB::table('post')->insert([
                        'USER_ID' => 3,
                        'CONTENT' => 'Bài viết có ID = 6', 
                        'TOPIC' => 5,
                        'LIKE_QUANTITY' => 60,
                        'IS_DELETED' => 0,
                        'TIME' => '2024-05-01 15:30:00'
                    ]);
                    //image post 2
                    {
                        DB::table('post_image')->insert([
                            "POST_ID" => 6,
                            "IMAGE_ID" => 20
                        ]);   
                    }
                }

                // post 7
                {
                    DB::table('post')->insert([
                        'USER_ID' => 3,
                        'CONTENT' => 'Bài viết có ID = 7', 
                        'TOPIC' => 1,
                        'LIKE_QUANTITY' => 70,
                        'IS_DELETED' => 0,
                        'TIME' => '2024-05-12 15:30:00'
                    ]);
                    //image post 2
                    {
                        DB::table('post_image')->insert([
                            "POST_ID" => 6,
                            "IMAGE_ID" => 19
                        ]);   
                    }
                }
    
            }
            // DB::table("VOUCHER")
            // ->insert([
            //     'VOUCHER_CODE' => 'VC_CTNQ_T1',
            //     'DISCOUNT_VALUE' => 10000, 
            //     'MAX_QUANTITY' => 100,
            //     'VOUCHER_CODE' => "CT_NGQ_T1",
            //     'SHOP_ID' => 1, 
            //     'START_DATE' => '2024-05-01',
            //     'EXPIRATION_DATE' => '2024-05-31',
            //     'REMAIN_AMOUNT' => 50
            // ]);

            
            //insert voucher
            {
                DB::table('voucher')->insert([
                    'VOUCHER_CODE' => 'CT_NGQ_T7',
                    'DISCOUNT_VALUE' => 10, 
                    'MAX_QUANTITY' => 100,
                    'SHOP_ID' => 1, 
                    'MIN_ORDER_TOTAL' => 50000,
                    'START_DATE' => '2024-07-01',
                    'EXPIRATION_DATE' => '2024-07-31',
                    'REMAIN_AMOUNT' => 90
                ]);
                DB::table('voucher')->insert([
                    'VOUCHER_CODE' => 'CT_NGQ_T8',
                    'DISCOUNT_VALUE' => 20, 
                    'MAX_QUANTITY' => 100,
                    'SHOP_ID' => 1, 
                    'MIN_ORDER_TOTAL' => 30000,
                    'START_DATE' => '2024-08-01',
                    'EXPIRATION_DATE' => '2024-08-31',
                    'REMAIN_AMOUNT' => 100
                ]);
            }

            //insert order 
            for($i = 1; $i <= 5; $i++) { 
                for($j = 1; $j <= 20; $j++) {
                    DB::table('order')->insert([ 
                        'USER_ID' => 1,
                        'ADDRESS_ID' => 1,
                        'PAYMENT_METHOD' => ( $j % 4 ) == 0 ? "Tiền mặt" : "Chuyển khoản",
                        'VOUCHER_CODE' => "CT_NGQ_T7",
                        'STATUS' => $i,
                        'PAYMENT_STATUS' => ( $j % 2 ) == 0 ? 1: 0 ,
                        'TOTAL_PAYMENT' => 21.98,
                        'NOTE' => 'Giao sớm giúp em!!',
                        'DATE' => \Carbon\Carbon::now()
                        // ->addDays($i)
                    ]);
                    DB::table('order_detail')->insert([
                        'ORDER_ID' => $j + ($i - 1) * 20,
                        'FAD_ID' => $i,
                        'QUANTITY' => 2,
                        'PRICE' => 10.99,
                    ]);
                }
            }

            //insert  POST_INTERACTION
            DB::table('POST_INTERACTION')->insert([
                'POST_ID' => 4,
                'USER_ID' => 1,
                'IS_LIKE' => 1, 
            ]);

            DB::table('POST_INTERACTION')->insert([
                'POST_ID' => 5,
                'USER_ID' => 1,
                'IS_LIKE' => 1, 
            ]);

            DB::table('POST_INTERACTION')->insert([
                'POST_ID' => 6,
                'USER_ID' => 1,
                'IS_LIKE' => 1, 
                'IS_SAVE' => 1, 
            ]);

            // DB::table('POST_INTERACTION')->insert([
            //     'POST_ID' => 5,
            //     'USER_ID' => 1,
            //     'IS_SAVE' => 1, 
            // ]);

 
        } 
    }
} 
