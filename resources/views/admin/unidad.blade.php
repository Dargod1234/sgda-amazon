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
                <h1 class="m-0">Mi Unidad</h1>
            </div>
            <div class="col-sm-6 text-end">
                <!-- Button trigger modal para crear carpeta -->
                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                    <i class="bi bi-folder-fill"></i> Crear Carpeta
                </button>
            </div>
        </div>

        <!-- Modal para crear nueva carpeta -->
        <div class="modal fade" id="createFolderModal" tabindex="-1" aria-labelledby="createFolderModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('mi_unidad.store') }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createFolderModalLabel">Nueva Carpeta</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nombre">Nombre de la Carpeta</label>
                                <input type="text" id="nombre" name="nombre" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="color">Color de la Carpeta</label>
                                <input type="color" id="color" name="color" class="form-control" value="#ebc034"> <!-- Color picker -->
                            </div>
                            <!-- Campo oculto para user_id -->
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Nueva Carpeta</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Listado de carpetas -->
        <hr>
        <h5>Carpetas</h5>

        @if ($carpetas->isNotEmpty())
            <div class="row py-4">
                @foreach ($carpetas as $carpeta)
                    <div class="col-md-3 mb-3">
                        <div class="card border-light shadow-sm">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <a href="{{ route('mi_unidad.show', $carpeta->id) }}" class="text-dark text-decoration-none me-2">
                                        <i class="bi bi-folder-fill" style="color: {{ $carpeta->color ?? '#ebc034' }}; font-size:20px;"></i>
                                    </a>
                                    <a href="{{ route('mi_unidad.show', $carpeta->id) }}" class="text-dark text-decoration-none">
                                        <h6 class="m-0">{{ $carpeta->nombre }}</h6>
                                    </a>
                                </div>
                            </div>
                        </div>                       
                    </div>
                @endforeach
            </div>
        @else
            <p>No hay carpetas disponibles.</p>
        @endif
    </div>
@endsection

@push('scripts')
<!-- Scripts adicionales si es necesario -->
<script>
    // Aquí puedes agregar scripts personalizados para interacciones si los necesitas
</script>
@endpush
