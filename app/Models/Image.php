<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory; 
    protected $table = 'Image';
    protected $primaryKey = 'IMAGE_ID';
    protected $fillable = ['IMAGE_ID', 'URL', 'USER_ID']; 
    public $timestamps = false;

    public function shopAVT()
    {
        return $this->belongsTo(Shop::class,  'SHOP_ID', 'AVT_IMAGE_ID');
    }
}
