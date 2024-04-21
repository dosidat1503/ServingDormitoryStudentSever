<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;
    protected $table = 'User'; 
    protected $fillable = ['name', 'email', 'phone', 'password', 'birthday', 'gender', 'AVT_IMAGE_ID', 'ADDRESS_ID', 'created_at']; 
}
