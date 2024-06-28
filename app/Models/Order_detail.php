<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order_detail extends Model
{
    use HasFactory;
    protected $table = 'Order_detail';
    
    protected $primaryKey = 'ORDER_DETAIL_ID';
    public $timestamps = false;
    protected $fillable = ['ORDER_ID', 'FAD_ID', 'QUANTITY', 'PRICE', 'ID_PARENT_OD_OF_THIS_OD', 'DATE', 'SIZE'];  

    public function fad()
    {
        return $this->belongsTo(FAD::class, 'FAD_ID');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'ORDER_ID');
    }
}
