<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coche extends Model
{
    use factory;
    protected $fillable = ['marca', 'modelo', 'anio', 'precio', 'foto'];
}
