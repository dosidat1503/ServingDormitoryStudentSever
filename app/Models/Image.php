<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory; 
    protected $table = 'Image';
    protected $primaryKey = 'IMAGE_ID';

    public function shopAVT()
    {
        return $this->belongsTo(Shop::class,  'SHOP_ID', 'AVT_IMAGE_ID');
    }
}
