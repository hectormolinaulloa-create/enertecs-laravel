<?php

namespace App\Mail;

use App\Models\CalculadoraSolicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevoLeadSolarMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly CalculadoraSolicitud $solicitud) {}

    public function envelope(): Envelope
    {
        $nombre = $this->solicitud->nombre ?? 'Cliente';
        $ahorro = number_format($this->solicitud->resultado['ahorro_mensual_clp'] ?? 0, 0, ',', '.');
        return new Envelope(
            subject: "Nuevo lead solar — {$nombre} (\${$ahorro}/mes)",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.nuevo-lead-solar');
    }
}
