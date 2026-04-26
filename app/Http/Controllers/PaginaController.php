<?php
namespace App\Http\Controllers;
use App\Models\Servicio;
use App\Models\Proyecto;

class PaginaController extends Controller
{
    public function home()
    {
        return view('pages.home', [
            'servicios'      => Servicio::activo()->get(),
            'certificaciones' => \App\Models\Certificacion::all(),
        ]);
    }

    public function nosotros()
    {
        return view('pages.nosotros');
    }

    public function servicios()
    {
        return view('pages.servicios', ['servicios' => Servicio::activo()->get()]);
    }

    public function servicio(string $slug)
    {
        $servicio = Servicio::where('slug', $slug)->where('activo', true)->firstOrFail();
        return view('pages.servicio', compact('servicio'));
    }

    public function experiencia()
    {
        $proyectos = Proyecto::activo()->get();
        return view('pages.experiencia', compact('proyectos'));
    }

    public function calculadora()
    {
        return view('pages.calculadora');
    }
}
