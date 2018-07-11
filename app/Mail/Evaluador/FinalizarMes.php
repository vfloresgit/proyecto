<?php

namespace App\Mail\Evaluador;
use App\StartUp;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class FinalizarMes extends Mailable 
{
    use Queueable, SerializesModels;
    public $user;
    public $startup;
    public $tipo;
    public $fecha;
    public $url;

    public function __construct(
        User $user,
        StartUp $startup,
        $tipo,
        $fecha,
        $url)
    {
        $this->user = $user;
        $this->startup = $startup;
        $this->url = $url;
        $this->tipo = $tipo;
        $this->fecha = $fecha;
    }

    public function build()
    {
        return $this->view('evaluador.finalizacionMes');
    }
}