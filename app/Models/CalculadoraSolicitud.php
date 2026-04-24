<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CalculadoraSolicitud extends Model
{
    protected $table    = 'calculadora_solicitudes';
    protected $fillable = ['uuid', 'nombre', 'email', 'telefono', 'empresa', 'datos_boleta', 'resultado', 'pdf_path', 'estado'];
    protected $casts    = ['datos_boleta' => 'array', 'resultado' => 'array'];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
