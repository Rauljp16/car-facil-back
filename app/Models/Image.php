<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use HasFactory;

    protected $fillable = ['coche_id', 'image_path'];

    public function coche()
    {
        return $this->belongsTo(Coche::class);
    }

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }

    public function delete()
    {
        Storage::disk('public')->delete($this->image_path);

        return parent::delete();
    }
}
