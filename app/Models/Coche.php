<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coche extends Model
{
    use HasFactory;

    protected $fillable = ['marca', 'modelo', 'anio', 'precio', 'cambio', 'combustible', 'motor', 'cv', 'plazas', 'puertas'];

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function delete()
    {
        $this->images()->delete();

        return parent::delete();
    }

    public function getPrecioAttribute($value)
    {
        return number_format($value, 0, ',', '.');
    }
}
