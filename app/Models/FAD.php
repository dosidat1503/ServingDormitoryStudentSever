<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FAD extends Model
{
    use HasFactory;
    protected $table = 'FAD';
    protected $primaryKey = 'FAD_ID';
    // By default laravel will expect created_at & updated_at column in your table
    public $timestamps = false;
    protected $fillable = [
        'FAD_NAME',
        'FAD_TYPE',
        'FAD_PRICE',
        'IMAGE_ID',
        'SHOP_ID',
        'ID_PARENTFADOFTOPPING',
        'DESCRIPTION',
        'IS_DELETED'
    ];



    // By default laravel will expect created_at & updated_at column in your table
    public $stamps = false;
    protected $fill = [
        'FAD_NAME',
        'FAD_TYPE',
        'FAD_PRICE',
        'IMAGE_ID',
        'SHOP_ID',
        'ID_PARENTFADOFTOPPING',
        'DESCRIPTION',
        'IS_DELETED'
    ];
    public function image()
    {
        return $this->hasOne(Image::class, 'IMAGE_ID', 'IMAGE_ID');
    }
}
