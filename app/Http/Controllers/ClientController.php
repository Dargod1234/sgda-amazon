<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use App\Imports\ClientsImport;
use Maatwebsite\Excel\Facades\Excel;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::latest()->paginate(10);
        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'legal_representative_phone' => 'required|max:255',
            'business_name' => 'required|max:255',
            'legal_representative_name' => 'required|max:255',
            'legal_representative_email' => 'required|max:255',
            'email' => 'required|email|unique:clients',
            'phone' => 'required|max:20',
            'nit_cc' => 'required|max:20',
            'contract_owner' => 'required|in:Jennifer,Igamocol,Rodolfo',
            'contract_start' => 'required|date',
            'contract_end' => 'required|date|after:contract_start',
            'address' => 'nullable|max:255',
            'notes' => 'nullable'
        ]);
    
        Client::create($validated);
    
        return redirect()->route('admin.clients.index')
            ->with('success', 'Cliente creado exitosamente');
    }

    public function edit(Client $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'legal_representative_phone' => 'required|max:255',
            'business_name' => 'required|max:255',
            'legal_representative_name' => 'required|max:255',
            'legal_representative_email' => 'required|max:255',
            'email' => 'required|email',
            'phone' => 'required|max:20',
            'nit_cc' => 'required|max:20',
            'contract_owner' => 'required|in:Jennifer,Igamocol,Rodolfo',
            'contract_start' => 'required|date',
            'contract_end' => 'required|date|after:contract_start',
            'address' => 'nullable|max:255',
            'notes' => 'nullable'
        ]);
    
        $client->update($validated);
    
        return redirect()->route('admin.clients.index')
            ->with('success', 'Cliente actualizado exitosamente');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('admin.clients.index')
            ->with('success', 'Cliente eliminado exitosamente');
    }

    public function importForm()
    {
        return view('admin.clients.import'); // Asegúrate de que esta vista exista
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            Excel::import(new ClientsImport, $request->file('file'));

            return redirect()->route('admin.clients.index')
                ->with('success', '¡Clientes importados exitosamente!');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $errors = [];
            foreach ($e->failures() as $failure) {
                $errors[] = "Fila {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return redirect()->back()
                ->with('error', 'Errores en el archivo')
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error crítico: ' . $e->getMessage());
        }
    }
}