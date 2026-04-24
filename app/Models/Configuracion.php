<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table    = 'configuraciones';
    protected $fillable = ['clave', 'valor'];

    public static function get(string $clave, string $default = ''): string
    {
        return static::where('clave', $clave)->value('valor') ?? $default;
    }
}
