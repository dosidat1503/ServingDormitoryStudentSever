<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FAD extends Model
{
    use HasFactory;
    protected $table = 'FAD';
    protected $primaryKey = 'FAD_ID';
    public function image()
    {
        return $this->hasOne(Image::class, 'IMAGE_ID', 'IMAGE_ID');
    }
}
