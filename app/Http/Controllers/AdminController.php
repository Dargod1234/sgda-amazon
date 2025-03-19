<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Carpeta;
use App\Models\Archivo;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Asegúrate de que solo usuarios autenticados puedan acceder
    }

    public function index()
    {
        // Verifica que el usuario sea admin
        if (Auth::user()->role !== 'admin') {
            return redirect('/login')->with('error', 'No tienes permiso para acceder a esta página.');
        }

        $users = User::all();
        return view('admin.index', compact('users'));
    }

    // Ver unidad de un usuario específico
    public function userUnidad($id)
    {
        // Verifica que el usuario sea admin
        if (Auth::user()->role !== 'admin') {
            return redirect('/login')->with('error', 'No tienes permiso para acceder a esta página.');
        }

        $user = User::findOrFail($id);
        $carpetas = Carpeta::where('user_id', $user->id)->get();
        $archivos = Archivo::whereIn('carpeta_id', $carpetas->pluck('id'))->get();

        return view('admin.unidad', compact('user', 'carpetas', 'archivos'));
    }
}
