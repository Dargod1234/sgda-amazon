<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;

class AppointmentUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;
    public $client;
    public $user;
    public $originalData;

    public function __construct(Appointment $appointment, Client $client, User $user, $originalData)
    {
        $this->appointment = $appointment;
        $this->client = $client;
        $this->user = $user;
        $this->originalData = $originalData;
    }

    public function build()
    {
        return $this->subject('Actualizacion de Cita - Data Igamocol')
            ->view('emails.appointment_updated');
    }
}