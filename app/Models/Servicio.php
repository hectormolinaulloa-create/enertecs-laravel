<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $fillable = ['nombre', 'slug', 'descripcion', 'icono', 'imagen', 'activo', 'orden'];
    protected $casts    = ['activo' => 'boolean', 'orden' => 'integer'];

    public function scopeActivo($query)
    {
        return $query->where('activo', true)->orderBy('orden');
    }
}
