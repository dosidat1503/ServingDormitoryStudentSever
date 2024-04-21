<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable; 

    protected $primaryKey = 'USER_ID';

    protected $table = 'User'; 
    const CREATED_AT = 'create_at'; 
    public $timestamps = false;
    
    protected $fillable = ['name', 'email', 'phone', 'password', 'birthday', 'gender', 'AVT_IMAGE_ID', 'ADDRESS_ID', 'created_at']; 
}
