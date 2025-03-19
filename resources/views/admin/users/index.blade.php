@extends('layouts.admin')
@section('content')

<br>
<div class="row col-md-12 pt-10">
    <h1>Administrador de Usuarios</h1>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Lista de usuarios</h3>
                <div class="card-tools">
                    <a href="{{ url('/admin/users/new') }}" class="btn btn-primary"><i class="bi bi-person-add"></i> Nuevo usuario</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-sm table-striped table-hover">
                    <thead>
                        <tr>
                            <th><center>Nro</center></th>
                            <th><center>Nombre</center></th>
                            <th><center>Email</center></th>
                            <th><center>Acciones</center></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $counter = 0; @endphp
                        @foreach($users as $user)
                            @php
                                $counter = $counter + 1;
                                $id = $user->id;
                            @endphp
                            <tr>
                                <td style="text-align: center">{{ $counter }}</td>
                                <td style="text-align: center">{{ $user->name }}</td>
                                <td style="text-align: center">{{ $user->email }}</td>
                                <td style="text-align: center">
                                    <div class="btn-group" role="group" aria-label="Basic example">
                                        <!-- Botón de Información -->
                                        @if (Auth::user()->role == 'admin')    
                                        <a href="{{ route('users.show', $user->id) }}"
                                           type="button"
                                           class="btn btn-info"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="top"
                                           title="Ver información del usuario">
                                           <i class="bi bi-info-circle"></i>
                                        </a>
 
                                        <!-- Botón de Editar -->
                                        <a href="{{ route('users.edit', $user->id) }}"
                                           type="button"
                                           class="btn btn-success"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="top"
                                           title="Editar usuario">
                                           <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <!-- Botón de Eliminar -->
                                        <form action="{{ route('users.destroy', $user->id) }}"
                                              onclick="ask(event, {{ $id }})"
                                              method="POST"
                                              id="myForm{{ $id }}"
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-danger"
                                                    style="border-radius: 0 5px 5px 0"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="Eliminar usuario">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>

                                        <!-- Botón de Permisos -->
                                        <a href="{{ route('user.permissions', $user->id) }}"
                                           type="button"
                                           class="btn btn-primary"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="top"
                                           title="Administrar permisos del usuario">
                                           <i class="bi bi-tree"></i>
                                        </a>


                                         <!-- Botón de Permisos -->
                                         <a href="{{ route('user.login-history', $user->id) }}"
                                            type="button"
                                            class="btn btn-warning"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="Historial e Informacion de Ingreso al Sistema">
                                            <i class="bi bi-fingerprint"></i>
                                         </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    function ask(event, id) {
        event.preventDefault();
        Swal.fire({
            title: 'Eliminar registro',
            text: 'Ten en cuenta que esta acción es irreversible y eliminará permanentemente al usuario y todas las actividades relacionadas(Archivos, Carpetas y Registro de Actividad).',
            icon: 'warning',
            showDenyButton: true,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: '#a5161d',
            denyButtonColor: '#270a0a',
            denyButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.getElementById('myForm' + id);
                form.submit();
            }
        });
    }
    
</script>

@endsection

