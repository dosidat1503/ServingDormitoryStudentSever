<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;
    protected $table = 'Voucher';
    protected $primaryKey = 'VOUCHER_ID';
    protected $fillable = ['VOUCHER_CODE', 'SHOP_ID', 'DISCOUNT_VALUE', 'MAX_DISCOUNT_VALUE', 'MIN_ORDER_TOTAL', 'START_DATE', 'EXPIRATION_DATE', 'MAX_QUANTITY', 'REMAIN_AMOUNT', 'IS_DELETED'];
    public $timestamps = false;

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'SHOP_ID', 'SHOP_ID');
    }
}
