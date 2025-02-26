<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = ['coche_id', 'image_path'];

    public function coche()
    {
        return $this->belongsTo(Coche::class);
    }
}
