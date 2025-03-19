<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Archivo;
use App\Models\LoginHistory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Verifica si el usuario es administrador
        if (auth()->user()->role === 'admin') {
            // Los administradores ven todos los usuarios
            $users = User::all();
        } elseif (auth()->user()->role === 'moderator') {
            // Los moderadores solo ven usuarios con rol 'user'
            $users = User::where('role', 'user')->get();
        } else {
            // Si el usuario no tiene un rol válido, no ve nada
            $users = collect(); // colección vacía
        }

        return view('admin.users.index', ['users' => $users]);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|unique:users',
            'password' => 'required|confirmed|min:8',
            'role' => auth()->user()->role === 'admin' ? 'required|in:admin,moderator,user' : 'nullable',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request['password']);

        // Asignar el rol seleccionado si es admin
        if (auth()->user()->role === 'admin') {
            $user->role = $request->role;
        } else {
            // Si no es admin (ej. un moderador), asignar 'user' por defecto
            $user->role = 'user';
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('alert', 'Se registró el usuario correctamente')
            ->with('icon', 'success');
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.show', ['user' => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
    
      
    
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
    
        // Validación
        $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id, // Asegurarse de que el email sea único, excluyendo el actual
            'password' => 'nullable|confirmed|min:8', // Solo se actualiza si se proporciona
            'role' => auth()->user()->role === 'admin' ? 'required|in:admin,moderator,user' : 'nullable',
        ]);
    
        // Actualizar los campos
        $user->name = $request->name;
        $user->email = $request->email;
    
        // Solo actualizar el rol si el usuario autenticado es admin
        if (auth()->user()->role === 'admin') {
            $user->role  = $request->role;
        }
    
        // Si se proporciona la contraseña, actualízala
        if ($request->filled('password')) {
            $user->password = Hash::make($request['password']);
        }
    
        // Guardar los cambios en la base de datos
        $user->save();
    
        return redirect()->route('users.index')
            ->with('mensaje', 'Se actualizó el usuario correctamente')
            ->with('icono', 'success');
    }




    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Verificar si el usuario tiene el ID 1
        if ($id == 1) {
            return redirect()->route('users.index')
                ->with('mensaje', 'El Super Administrador no se puede eliminar!.')
                ->with('icono', 'warning');
        }
    
        $user = User::findOrFail($id);
    
        try {
            // Primero, eliminar los logs de actividades que están vinculados a los archivos
            $user->activityLogs()->delete();
    
            // Eliminar archivos asociados al usuario
            $user->archivos()->each(function ($archivo) {
                // Eliminar los registros en activity_logs asociados a este archivo
                $archivo->activityLogs()->delete();
                
                // Eliminar las carpetas relacionadas al archivo
                $archivo->carpeta()->delete();
                
                // Eliminar el archivo
                $archivo->delete();
            });
    
            // Eliminar carpetas asociadas directamente al usuario
            $user->carpetas()->each(function ($carpeta) {
                $carpeta->delete();  // Eliminar carpeta
            });
    
            // Finalmente, eliminar al usuario
            $user->delete();
    
            return redirect()->route('users.index')
                ->with('mensaje', 'Se eliminó el usuario correctamente junto con sus carpetas, archivos y actividades.')
                ->with('icono', 'success');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('mensaje', 'No se pudo eliminar el usuario: ' . $e->getMessage())
                ->with('icono', 'error');
        }
    }


    public function showLoginHistory($id)
    {
        $loginHistory = LoginHistory::where('user_id', $id)
            ->orderBy('created_at', 'desc') // Ordenar por el más reciente
            ->paginate(10); // Paginación de 20 registros por página
    
        return view('admin.users.login-history', compact('loginHistory'));
    }
}
