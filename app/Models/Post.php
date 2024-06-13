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
    
    protected $fillable = ['USER_ID', 'CONTENT', 'TOPIC']; 

    public function postImages()
    {
        return $this->hasMany(Post_Image::class, 'POST_ID');
    }  
}
