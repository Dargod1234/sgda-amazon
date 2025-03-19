@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Crear Nuevo Activo</h1>
    
    <form action="{{ route('admin.activos.store') }}" method="POST">
        @csrf
        
        <div class="mb-3">
            @if(request()->has('client_id'))
                @php $client_id = request('client_id') @endphp
                <input type="hidden" name="client_id" value="{{ $client_id }}">
                <label class="form-label">Cliente</label>
                <input type="text" class="form-control" 
                       value="{{ App\Models\Client::find($client_id)->name }}" readonly>
            @else
                <label class="form-label">Cliente</label>
                <select name="client_id" class="form-select" required>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                        {{ $client->name }}
                    </option>
                    @endforeach
                </select>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label">Nombre del Activo</label>
            <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Descripci¨®n</label>
            <textarea name="descripcion" class="form-control">{{ old('descripcion') }}</textarea>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Cantidad</label>
            <input type="number" name="cantidad" class="form-control" value="{{ old('cantidad', 1) }}" min="1" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Fecha de Caducidad</label>
            <input type="date" name="fecha_expiracion" class="form-control" value="{{ old('fecha_expiracion') }}">
        </div>
        
        <button type="submit" class="btn btn-primary">Crear Activo</button>
    </form>
</div>
@endsection