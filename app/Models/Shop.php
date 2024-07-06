<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;
    protected $table = 'Shop';
    protected $primaryKey = 'SHOP_ID';
    protected $fillable = ['SHOP_NAME', 'PHONE', 'OPENTIME', 'CLOSETIME', 'AVT_IMAGE_ID', 'COVER_IMAGE_ID', 'SHOP_OWNER_ID', 'ADDRESS_ID', 'DESCRIPTION', 'IS_DELETED', 'created_at'];
    public $timestamps = false;
    // public function image(){
    //     return $this->hasOne(Image::class, 'IMAGE_ID');
    // }
    public function image()
    {
        return $this->hasOne(Image::class, 'IMAGE_ID');
    }
    // public function image(){
    //     return $this->hasOne(Image::class, 'IMAGE_ID');
    // }
    public function user()
    {
        return $this->belongsTo(User::class, 'SHOP_OWNER_ID', 'USER_ID');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'ADDRESS_ID', 'ADDRESS_ID');
    }

    public function imageCover()
    {
        return $this->belongsTo(Image::class, 'COVER_IMAGE_ID', 'IMAGE_ID');
    }
}
