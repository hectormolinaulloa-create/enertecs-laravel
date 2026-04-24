<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalculadoraSolicitud extends Model
{
    protected $table    = 'calculadora_solicitudes';
    protected $fillable = ['nombre', 'email', 'telefono', 'empresa', 'datos_boleta', 'resultado', 'pdf_path', 'estado'];
    protected $casts    = ['datos_boleta' => 'array', 'resultado' => 'array'];
}
