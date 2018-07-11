<?php

namespace App\Mail\Evaluador;
use App\StartUp;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AsignarStartup extends Mailable 
{
    use Queueable, SerializesModels;
    public $user;
    public $startup;
    public $url;

    public function __construct(User $user, StartUp $startup,$url)
    {
        $this->user = $user;
        $this->startup = $startup;
        $this->url = $url;
    }

    public function build()
    {
        return $this->view('evaluador.asignarStartup');
    }
}