@extends('layouts.admin')

@section('content')
    <div class="container px-4 py-4">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Carpeta: {{ $carpeta->nombre }}</h1>
            </div>
            <div class="col-sm-6 text-end">
                <!-- Botón para restaurar carpeta -->
                <form action="{{ route('carpetas.restaurar', $carpeta->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-arrow-up-circle"></i> Restaurar Carpeta
                    </button>
                </form>
            </div>
        </div>

        <h3>Archivos</h3>
        @if ($carpeta->archivos->isNotEmpty())
            <ul class="list-group mb-3">
                @foreach ($carpeta->archivos as $archivo)
                    <li class="list-group-item">{{ $archivo->nombre }}</li>
                @endforeach
            </ul>
        @else
            <p>No hay archivos en esta carpeta.</p>
        @endif
        
        <h3>Subcarpetas</h3>
        @if ($carpeta->carpetasHijas->isNotEmpty())
            <ul class="list-group mb-3">
                @foreach ($carpeta->carpetasHijas as $subcarpeta)
                    <li class="list-group-item">
                        <a href="{{ route('carpetas.obsoletas.show', $subcarpeta->id) }}" class="text-dark text-decoration-none">
                            <i class="bi bi-folder-fill" style="color: {{ $subcarpeta->color ?? '#ebc034' }}; font-size:16px;"></i>
                            {{ $subcarpeta->nombre }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <p>No hay subcarpetas en esta carpeta.</p>
        @endif
    </div>
@endsection
