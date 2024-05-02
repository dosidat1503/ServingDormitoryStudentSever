<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $table = 'Post';
    const CREATED_AT = 'create_at'; 
    public $timestamps = false;
    
    protected $fillable = ['USER_ID', 'CONTENT', 'IMAGE_ID', 'TOPIC']; 
}
