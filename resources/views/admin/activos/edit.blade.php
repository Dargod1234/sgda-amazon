@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Editar Activo</h1>
    
    <form action="{{ route('admin.activos.update', $activo) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <input type="hidden" name="client_id" value="{{ $activo->client_id }}">
            <label class="form-label">Cliente</label>
            <input type="text" class="form-control" 
                   value="{{ $activo->client->name }}" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Nombre del Activo</label>
            <input type="text" name="nombre" class="form-control" 
                   value="{{ old('nombre', $activo->nombre) }}" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control">{{ old('descripcion', $activo->descripcion) }}</textarea>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Cantidad</label>
            <input type="number" name="cantidad" class="form-control" 
                   value="{{ old('cantidad', $activo->cantidad) }}" min="1" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Fecha de Caducidad</label>
            <input type="date" name="fecha_expiracion" class="form-control" 
                   value="{{ old('fecha_expiracion', $activo->fecha_expiracion) }}">
        </div>
        
        <button type="submit" class="btn btn-primary">Actualizar Activo</button>
    </form>
</div>
@endsection