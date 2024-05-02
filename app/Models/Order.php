<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'Order';
    protected $primaryKey = 'ORDER_ID';
 
    public function orderDetails()
    {
        return $this->hasMany(Order_detail::class, 'ORDER_ID');
    }  
}
