<?php
namespace App\Livewire;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class ContactForm extends Component
{
    public string $nombre  = '';
    public string $email   = '';
    public string $mensaje = '';
    public bool   $enviado = false;
    public string $error   = '';

    protected $rules = [
        'nombre'  => 'required|string|max:100',
        'email'   => 'required|email:rfc|max:150',
        'mensaje' => 'required|string|min:10|max:2000',
    ];

    public function enviar(): void
    {
        $this->validate();
        $destino = Configuracion::get('email_contacto', config('mail.from.address'));

        try {
            Mail::raw(
                "Nombre: {$this->nombre}\nEmail: {$this->email}\n\nMensaje:\n{$this->mensaje}",
                function ($m) use ($destino) {
                    $m->to($destino)
                      ->replyTo($this->email, $this->nombre)
                      ->subject("Contacto Web — {$this->nombre}");
                }
            );
            $this->reset(['nombre', 'email', 'mensaje']);
            $this->enviado = true;
        } catch (\Throwable $e) {
            $this->error = 'No se pudo enviar el mensaje. Inténtalo más tarde.';
        }
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
