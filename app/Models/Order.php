<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'Order';
    protected $primaryKey = 'ORDER_ID';
    public $timestamps = false;
    protected $fillable = ['USER_ID', 'ORDER_ADDRESS_ID', 'VOUCHER_ID', 'PAYMENT_METHOD', 'STATUS', 'TOTAL_PAYMENT']; 
 
    public function orderDetails()
    {
        return $this->hasMany(Order_detail::class, 'ORDER_ID');
    }  
}
