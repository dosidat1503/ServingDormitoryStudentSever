<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FAD extends Model
{
    use HasFactory;
    protected $table = 'FAD';
    protected $primaryKey = 'FAD_ID';
    public $timestamps = false;
    protected $fillable = [
        'FAD_NAME',
        'FAD_PRICE',
        'IMAGE_ID',
        'SHOP_ID',
        'ID_PARENTFADOFTOPPING',
        'ID_PARENTFADOFSIZE',
        'DESCRIPTION',
        'IS_DELETED',
        'QUANTITY',
        'CATEGORY',
        'TAG',
        'DATE'
    ];
    public function image()
    {
        return $this->hasOne(Image::class, 'IMAGE_ID', 'IMAGE_ID');
    }
}
