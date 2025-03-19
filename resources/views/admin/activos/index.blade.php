@extends('layouts.admin')

@section('content')
<div class="container">
    @isset($client)
        <div class="alert alert-info mb-4">
            <h2>Activos de: {{ $client->name }} {{ $client->nit_cc }}</h2>
            <a href="{{ route('admin.clients.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Clientes
            </a>
        </div>
    @else
        <h1>Todos los Activos</h1>
    @endisset
    
    <a href="{{ route('admin.activos.create') }}" class="btn btn-primary mb-3">Nuevo Activo</a>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Nombre</th>
                <th>Cantidad</th>
                <th>Caducidad</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activos as $activo)
            <tr>
                <td>{{ $activo->client->name }}</td>
                <td>{{ $activo->nombre }}</td>
                <td>{{ $activo->cantidad }}</td>
                <td>{{ $activo->fecha_expiracion}}</td>
                <td>
                    <a href="{{ route('admin.activos.edit', $activo) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('admin.activos.destroy', $activo) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('���Eliminar activo?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    {{ $activos->links() }}
</div>
@endsection