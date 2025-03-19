<?php

namespace App\Http\Controllers;

use App\Models\Activos;
use App\Models\Client;
use Illuminate\Http\Request;

class ActivoController extends Controller
{
    public function index(Client $client)
    {
        $activos = $client->activos()->paginate(10);
        return view('admin.activos.index', compact('activos', 'client'));
    }

    public function create(Request $request)
    {
        $client_id = $request->input('client_id');
        $clients = $client_id ? Client::where('id', $client_id)->get() : Client::all();
        
        return view('admin.activos.create', compact('clients', 'client_id'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'nombre' => 'required|max:255',
            'descripcion' => 'nullable',
            'cantidad' => 'required|numeric|min:1',
            'fecha_expiracion' => 'nullable|date'
        ]);

        Activos::create($validated);

        return redirect()->route('admin.activos.index')
            ->with('success', 'Activo creado exitosamente');
    }

    public function edit(Activos $activo)
    {
        $clients = Client::all();
        return view('admin.activos.edit', compact('activo', 'clients'));
    }


    public function update(Request $request, Activos $activo)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'nombre' => 'required|max:255',
            'descripcion' => 'nullable',
            'cantidad' => 'required|numeric|min:1',
            'fecha_expiracion' => 'nullable|date'
        ]);

        $activo->update($validated);

        return redirect()->route('admin.activos.index')
            ->with('success', 'Activo actualizado exitosamente');
    }

    public function destroy(Activo $asset)
    {
        $asset->delete();
        return redirect()->route('admin.activos.index')
            ->with('success', 'Activo eliminado exitosamente');
    }
}