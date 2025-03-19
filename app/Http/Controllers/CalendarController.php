<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentCreatedMail;
use App\Mail\AppointmentCancelledMail;
use App\Mail\AppointmentUpdatedMail;


class CalendarController extends Controller
{
    public function index()
    {
        $clients = Client::all();
        $users = User::all();
        return view('admin.calendar.index', compact('clients', 'users'));
    }

    public function getAppointments()
    {
        $appointments = Appointment::with(['client', 'user'])
            ->get(['id', 'title', 'description', 'start', 'end', 'color', 'client_id', 'user_id']);

        return response()->json($appointments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'client_id' => 'required|exists:clients,id',
            'user_id' => 'required|exists:users,id'
        ]);

        $appointment = Appointment::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'start' => $validated['start'],
            'end' => $validated['end'],
            'color' => $this->getRandomColor(),
            'client_id' => $validated['client_id'],
            'user_id' => $validated['user_id']
        ]);
        $client = Client::findOrFail($validated['client_id']);
        $user = User::findOrFail($validated['user_id']);
    
        // Enviar correo
        try {
                Mail::to([$client->email, $client->legal_representative_email])
                ->send(new AppointmentCreatedMail($appointment, $client, $user));
            } catch (\Exception $e) {
                \Log::error('Error enviando correo: ' . $e->getMessage());
            }

    
        return response()->json($appointment);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $originalData = $appointment->getOriginal();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'client_id' => 'required|exists:clients,id',
            'user_id' => 'required|exists:users,id'
        ]);

        $appointment->update($validated);

        $client = Client::find($appointment->client_id);
        $user = User::find($appointment->user_id);

        try {
            Mail::to([$client->email, $client->legal_representative_email])
                ->send(new AppointmentUpdatedMail($appointment, $client, $user, $originalData));
        } catch (\Exception $e) {
            \Log::error('Error enviando correo de actualizaci®Æn: ' . $e->getMessage());
        }

        return response()->json($appointment);
    }

    public function destroy($id)
    {
        $appointment = Appointment::find($id);
        
        if ($appointment) {
            $client = Client::find($appointment->client_id);
            $user = User::find($appointment->user_id);

            try {
                Mail::to([$client->email, $client->legal_representative_email])
                    ->send(new AppointmentCancelledMail($appointment, $client, $user));
            } catch (\Exception $e) {
                \Log::error('Error enviando correo de cancelaci®Æn: ' . $e->getMessage());
            }

            $appointment->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Cita no encontrada'], 404);
    }
    // M√©todo para generar un color aleatorio
    private function getRandomColor()
    {
        // Array de posibles colores claros que contrastan bien con letras negras
        $colors = [
            '#FAD02E', // Amarillo
            '#FFC857', // Naranja claro
            '#FFB5A7', // Rosa suave
            '#A0E8AF', // Verde suave
            '#B5EAEA', // Azul claro
            '#FFD6E0', // Rosa claro
            '#FFEF9F', // Amarillo pastel
            '#9AE1FF', // Azul celeste
            '#E7FBBE', // Verde claro
            '#D4A5A5', // Rosa apagado
            '#FFDDC1', // Melocot√≥n suave
            '#F8F0E3', // Beige suave
            '#CAF7E3', // Verde menta
            '#F6D6AD', // Naranja pastel
            '#FFF3B0', // Amarillo claro
            '#9ADCFF', // Azul cielo
            '#D4F1F4', // Azul suave
            '#FFE7C7', // Melocot√≥n
            '#C5E1A5', // Verde lima claro
            '#E1F5C4', // Verde lima pastel
        ];

        return $colors[array_rand($colors)]; // Selecciona un color aleatorio
    }
}
