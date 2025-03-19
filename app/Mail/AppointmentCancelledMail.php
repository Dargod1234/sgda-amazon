<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;

class AppointmentCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;
    public $client;
    public $user;

    public function __construct(Appointment $appointment, Client $client, User $user)
    {
        $this->appointment = $appointment;
        $this->client = $client;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Cancelacion de Cita - Data Igamocol')
            ->view('emails.appointment_cancelled');
    }
}