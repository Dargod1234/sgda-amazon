@extends('layouts.admin')

@section('content')
    <div class="container px-4 py-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulario de búsqueda -->
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Mi Unidad</h1>
            </div>
            <div class="col-sm-6">
                <form method="GET" action="{{ route('mi_unidad.search') }}" class="float-sm-right">
                    <input type="text" name="search" class="form-control" placeholder="Buscar carpeta o archivo" value="{{ request('search') }}">
                </form>
            </div>
        </div>

        <hr>

        @if (isset($searchTerm) && $searchTerm)
            <p>Resultados de búsqueda para: <strong>{{ $searchTerm }}</strong></p>
        @endif

        <!-- Mostrar Carpetas -->
        @if ($carpetas->isNotEmpty())
            <h5>Carpetas Encontradas</h5>
            <div class="row py-4">
                @foreach ($carpetas as $carpeta)
                    <div class="col-md-3">
                        <div class="divcontent bg-white border border-light rounded p-3 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ url('admin/mi_unidad/carpeta/' . $carpeta->id) }}"
                                    class="text-dark text-decoration-none d-flex align-items-center">
                                    <i class="bi bi-folder-fill me-2"
                                        style="color: {{ $carpeta->color ?? '#ebc034' }}; font-size:20px;"></i>
                                    <span>{{ $carpeta->nombre }}</span>
                                </a>
                                <div class="dropdown">
                                    <button class="btn dropdown text-decoration-none" type="button" data-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <button class="dropdown-item" type="button" data-toggle="modal"
                                            data-target="#modal_editar_{{ $carpeta->id }}">
                                            <i class="bi bi-pencil" style="margin:3px"></i> Editar
                                        </button>
                                        <button class="dropdown-item" type="button" data-toggle="modal"
                                            data-target="#modal_eliminar_{{ $carpeta->id }}">
                                            <i class="bi bi-trash" style="margin:3px"></i> Eliminar
                                        </button>
                                        <button class="dropdown-item" type="button" data-toggle="modal"
                                            data-target="#modal_obsoleta_{{ $carpeta->id }}">
                                            <i class="bi bi-archive" style="margin:3px"></i> Volver Obsoleta
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal para confirmar la acción de volver obsoleta -->
                        <div class="modal fade" id="modal_obsoleta_{{ $carpeta->id }}" tabindex="-1"
                            aria-labelledby="modalObsoletaLabel_{{ $carpeta->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('carpetas.obsoleta', $carpeta->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalObsoletaLabel_{{ $carpeta->id }}">Volver
                                                Carpeta Obsoleta</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Estás seguro de que deseas volver obsoleta la carpeta
                                                "{{ $carpeta->nombre }}"?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-warning">Volver Obsoleta</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Modal editar -->
                        <div class="modal fade" id="modal_editar_{{ $carpeta->id }}" tabindex="-1"
                            aria-labelledby="exampleModalLabel_{{ $carpeta->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('mi_unidad.update_folder') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" value="{{ $carpeta->id }}" name="id" hidden>
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel_{{ $carpeta->id }}">Editar
                                                Carpeta</h5>
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="nombre">Nombre de la Carpeta</label>
                                                <input type="text" id="nombre_{{ $carpeta->id }}" name="nombre"
                                                    class="form-control" value="{{ $carpeta->nombre }}" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Modal eliminar -->
                        <div class="modal fade" id="modal_eliminar_{{ $carpeta->id }}" tabindex="-1"
                            aria-labelledby="modalEliminarLabel_{{ $carpeta->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalEliminarLabel_{{ $carpeta->id }}">Confirmar
                                            Eliminación</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        ¿Está seguro de que desea eliminar la carpeta "{{ $carpeta->nombre }}"? Esta acción
                                        no se puede deshacer.
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-dismiss="modal">Cancelar</button>
                                        <form action="{{ route('mi_unidad.destroy', $carpeta->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Eliminar Carpeta</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted">No se encontraron carpetas.</p>
        @endif

        @if (!empty($archivos) && count($archivos) > 0)
        <hr>
        <h5>Archivos Encontrados</h5>
        <div class="row py-4">
            @foreach ($archivos as $archivo)
                <div class="col-md-3">
                    <div class="divcontent bg-white border border-light rounded p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="d-flex align-items-center">
                                <!-- Icono de archivo según tipo -->
                                @php
                                    $extension = strtolower(pathinfo($archivo->nombre, PATHINFO_EXTENSION));
                                    $icon = '';
                                    
                                    // Determinar el icono según la extensión
                                    switch ($extension) {
                                        case 'doc':
                                        case 'docx':
                                            $icon = 'bi bi-file-earmark-word'; // Icono de Word
                                            break;
                                        case 'xls':
                                        case 'xlsx':
                                            $icon = 'bi bi-file-earmark-excel'; // Icono de Excel
                                            break;
                                        case 'ppt':
                                        case 'pptx':
                                            $icon = 'bi bi-file-earmark-ppt'; // Icono de PowerPoint
                                            break;
                                        case 'pdf':
                                            $icon = 'bi bi-file-earmark-pdf'; // Icono de PDF
                                            break;
                                        default:
                                            $icon = 'bi bi-file-earmark'; // Icono por defecto
                                    }
                                @endphp
    
                                <i class="{{ $icon }} me-2" style="font-size: 20px;"></i>
                                <span>{{ $archivo->nombre }}</span>
                            </span>
                            <!-- Botón de ver con icono de ojo -->
                            <a href="{{ route('archivo.show', $archivo->id) }}" class="btn btn-info btn-sm">
                                <i class="bi bi-eye"></i> <!-- Icono de ojo -->
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-muted">No se encontraron archivos.</p>
    @endif

        @if ($carpetas->isEmpty() && $archivos->isEmpty())
            <p class="text-muted">No se encontraron resultados.</p>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // Puedes agregar scripts adicionales aquí si es necesario
    </script>
@endpush
