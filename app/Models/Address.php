<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    
    protected $table = 'ADDRESS';
    protected $fillable = ['NAME', 'PHONE', 'DETAIL', 'COMMUNE', 'DISTRICT', 'PROVINCE', 'IS_DELETED', 'IS_DEFAULT', 'USER_ID'];  
 
    public $timestamps = false;
}
