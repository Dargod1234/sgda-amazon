@extends('layouts.admin')

@section('content')
    <div class="container px-4 py-4">
        <!-- Muestra los errores -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Carpetas Obsoletas</h1>
            </div>
            <div class="col-sm-6 text-end">

            </div>
        </div>

        <hr>
        <h5>Lista de Carpetas Obsoletas</h5>

        @if ($carpetasObsoletas->isNotEmpty())
            <div class="row py-4">
                @foreach ($carpetasObsoletas as $carpeta)
                    <div class="col-md-3 mb-3">
                        <div class="card border-light shadow-sm">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <a href="{{ route('carpetas.obsoletas.show', $carpeta->id) }}" class="text-dark text-decoration-none me-2">
                                        <i class="bi bi-folder-fill" style="color: {{ $carpeta->color ?? '#ebc034' }}; font-size:20px;"></i>
                                    </a>
                                    <a href="{{ route('carpetas.obsoletas.show', $carpeta->id) }}" class="text-dark text-decoration-none">
                                        <h6 class="m-0">{{ $carpeta->nombre }}</h6>
                                    </a>
                                    <div class="dropdown">
                                        <button class="btn dropdown text-decoration-none" type="button" data-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <button class="dropdown-item" type="button" data-toggle="modal" data-target="#modal_restaurar_{{ $carpeta->id }}">
                                                <i class="bi bi-pencil" style="margin:3px"></i> Restaurar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal para restaurar carpeta -->
                        <div class="modal fade" id="modal_restaurar_{{ $carpeta->id }}" tabindex="-1" aria-labelledby="modalRestaurarLabel_{{ $carpeta->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('carpetas.restaurar', $carpeta->id) }}" method="POST">
                                    @csrf
                                    @method('POST') <!-- Asegúrate de que tu método sea PUT si estás actualizando el estado -->
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalRestaurarLabel_{{ $carpeta->id }}">Restaurar Carpeta</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Estás seguro de que deseas restaurar la carpeta <strong>{{ $carpeta->nombre }}</strong>?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Restaurar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p>No hay carpetas obsoletas disponibles.</p>
        @endif
    </div>
@endsection
