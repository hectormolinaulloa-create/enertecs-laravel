<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $fillable = ['nombre', 'cliente', 'categoria', 'lat', 'lng', 'año', 'descripcion', 'activo'];
    protected $casts    = ['activo' => 'boolean', 'lat' => 'float', 'lng' => 'float', 'año' => 'integer'];

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}
