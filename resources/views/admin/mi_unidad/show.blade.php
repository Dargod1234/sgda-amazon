@extends('layouts.admin')
<style>
.hidden {
    all: unset !important;  /* Desactiva todos los estilos aplicados */
    display:none !important;
    visibility: hidden !important;  /* Hace el elemento invisible */
    opacity: 0 !important;  /* Asegura que sea completamente invisible */
    pointer-events: none !important;  /* Deshabilita interacciones */
    text-decoration: none !important;  /* Elimina cualquier subrayado */
}
</style>
@section('content')

    <div class="container py-4">
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
                <h1 class="m-0">{{ $carpeta->nombre }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="float-sm-right">
                    <button onclick="window.history.back();" class="btn btn-default">
                        <i class="bi bi-arrow-left"></i> Volver
                    </button>

                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal_crear_carpeta">
                        <i class="bi bi-folder-fill"></i> Crear Carpeta
                    </button>

                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#uploadModal">
                        <i class="bi bi-cloud-upload-fill"></i> Subir Archivos
                    </button>
                </ol>
            </div>
        </div>

        <!-- Resto del código del modal para subir archivos y crear carpetas sigue igual -->
        <!-- Modal para subir archivos -->
        <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadModalLabel">Subir Archivos</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Información de la carpeta de destino -->
                        <div class="form-group">
                            <label for="folder">Carpeta Destino</label>

                            <h6>{{ $carpeta->nombre }}</h6>
                        </div>


                        <form action="{{ route('archivo.upload_file') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label for="fileInput" class="btn btn-primary btn-block">
                                    <i class="bi bi-cloud-upload"></i> Seleccionar Archivos
                                </label>
                                <input type="file" name="file[]" id="fileInput" class="d-none" multiple />
                                <small class="form-text text-muted">Arrastra y suelta los archivos o haz clic para
                                    seleccionarlos. Puedes seleccionar varios archivos.</small>
                                <div id="fileList" class="mt-3"></div>
                                <input type="hidden" name="folder_id" value="{{ $carpeta->id }}">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Subir Archivo</button>
                    </div>
                    </form>

                </div>
            </div>
        </div>


        <!-- Modal para Crear Carpeta -->
        <div class="modal fade" id="modal_crear_carpeta" tabindex="-1" aria-labelledby="modalCrearCarpetaLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('mi_unidad.store_subfolder') }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalCrearCarpetaLabel">Nueva Carpeta</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="nombre">Nombre de la Carpeta</label>
                                        <input type="text" id="nombre" name="nombre" class="form-control" required>
                                        <input type="hidden" id="carpeta_padre_id" name="carpeta_padre_id"
                                            value="{{ $carpeta->id }}" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="color">Color de la Carpeta</label>
                                        <div id="color-picker"></div> <!-- Elemento para el picker -->
                                        <input type="hidden" id="color" name="color" value="#ebc034">
                                        <!-- Campo oculto para almacenar el valor seleccionado -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Nueva Carpeta</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <hr>
    <h5>Subcarpetas</h5>

    <style>
        .custom-file-input {
            cursor: pointer;
        }

        .custom-file-input::before {
            content: 'Selecciona archivos';
            display: block;
            width: 100%;
            height: 100%;
            background: #007bff;
            color: white;
            text-align: center;
            line-height: 2.5;
            border-radius: 0.25rem;
            font-size: 1rem;
        }

        .custom-file-input::after {
            content: '';
        }

        .custom-file-input:focus {
            outline: none;
            box-shadow: none;
        }

        .custom-file-input::file-selector-button {
            display: none;
        }

        .dropdown-toggle {
            background: white;
            border: 0;
        }

        .dropdown-toggle::after {
            display: none;
        }

        .col-2 {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .divcontent {
            background: white;
            border: 1px solid #c0c0c0;
            border-radius: 10px;
            margin: 2px;
        }

        .divcontent:hover {
            box-shadow: 0 0 7px rgba(0, 0, 0, 0.5);
            transition: box-shadow 0.3s;
        }
    </style>

    @if ($subcarpetas->isNotEmpty())
        <div class="row my-4">
            @foreach ($subcarpetas as $subcarpeta)
                <div class="col-md-3 mb-3 mx-2 divcontent">
                    <div class="row d-flex justify-content-between" style="padding: 12px">
                        <div class="col-2">
                            <a href="{{ url('admin/mi_unidad/carpeta/' . $subcarpeta->id) }}" style="color:black;">
                                <i class="bi bi-folder-fill me-2"
                                    style="color: {{ $subcarpeta->color ?? '#ebc034' }}; font-size:20px;"></i>
                            </a>
                        </div>
                        <div class="col-8" style="margin-top:5px">
                            <a href="{{ url('admin/mi_unidad/carpeta/' . $subcarpeta->id) }}" style="color:black;">
                                <h6>{{ $subcarpeta->nombre }}</h6>
                            </a>
                        </div>
                        <div class="col-2">
                            <div class="dropdown">
                                <button class="dropdown-toggle" type="button" data-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <button class="dropdown-item" type="button" data-toggle="modal"
                                        data-target="#modal_editar_{{ $subcarpeta->id }}">
                                        <i class="bi bi-pencil" style="margin:3px"></i> Editar
                                    </button>
                                    <button class="dropdown-item" type="button" data-toggle="modal"
                                        data-target="#modal_eliminar_{{ $subcarpeta->id }}">
                                        <i class="bi bi-trash" style="margin:3px"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modales para editar y eliminar carpetas siguen igual -->
                    <div class="modal fade" id="modal_editar_{{ $subcarpeta->id }}" tabindex="-1"
                        aria-labelledby="modalEditarCarpetaLabel_{{ $subcarpeta->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form action="{{ route('mi_unidad.update_subfolder') }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" value="{{ $subcarpeta->id }}" name="id">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalEditarCarpetaLabel_{{ $subcarpeta->id }}">
                                            Editar
                                            Carpeta</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="nombre">Nombre de la Carpeta</label>
                                                    <input type="text" id="nombre_{{ $subcarpeta->id }}"
                                                        name="nombre" class="form-control"
                                                        value="{{ $subcarpeta->nombre }}" required>
                                                </div>

                                            </div>
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
                    <div class="modal fade" id="modal_eliminar_{{ $subcarpeta->id }}" tabindex="-1"
                        aria-labelledby="modalEliminarLabel_{{ $subcarpeta->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalEliminarLabel_{{ $subcarpeta->id }}">Confirmar
                                        Eliminación</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    ¿Está seguro de que desea eliminar la carpeta "{{ $subcarpeta->nombre }}"? Esta acción
                                    no
                                    se puede deshacer.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-dismiss="modal">Cancelar</button>
                                    <!-- Formulario para eliminar carpeta -->
                                    <form action="{{ route('mi_unidad.destroy_subfolder', $subcarpeta->id) }}"
                                        method="POST">
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
        <p class="text-muted">No hay sub-carpetas disponibles para seleccionar.</p>
    @endif

    <h5>Archivos en esta Carpeta</h5>

    <div class="container py-4">
        <div class="mb-3">
        <!-- Campo de búsqueda -->
        <input type="text" id="searchInput" placeholder="Buscar archivo..." class="form-control mb-3">
    </div>
    @if ($archivos->isNotEmpty())
        <div class="list-group" style="max-height: 400px; overflow-y: auto;">
            @foreach ($archivos as $archivo)
                <div class="list-group-item d-flex align-items-center mb-3">
                    @php
                        $fileExtension = pathinfo($archivo->nombre, PATHINFO_EXTENSION);
                    @endphp

                    <!-- Íconos más pequeños -->
                    @if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'jfif']))
                        <img src="{{ asset('dist/img/icons/img.svg') }}" class="img-fluid" alt="img file" style="width: 24px; height: 24px;">
                    @elseif (in_array($fileExtension, ['doc', 'docx']))
                        <img src="{{ asset('dist/img/icons/word.svg') }}" class="img-fluid" alt="Word file" style="width: 24px; height: 24px;">
                    @elseif (in_array($fileExtension, ['xls', 'xlsx']))
                        <img src="{{ asset('dist/img/icons/excel.svg') }}" class="img-fluid" alt="Excel file" style="width: 24px; height: 24px;">
                    @elseif (in_array($fileExtension, ['pdf']))
                        <img src="{{ asset('dist/img/icons/pdf.svg') }}" class="img-fluid" alt="PDF file" style="width: 24px; height: 24px;">
                    @elseif (in_array($fileExtension, ['txt']))
                        <img src="{{ asset('dist/img/icons/txt.svg') }}" class="img-fluid" alt="TXT file" style="width: 24px; height: 24px;">
                    @elseif (in_array($fileExtension, ['zip', 'rar']))
                        <img src="{{ asset('dist/img/icons/rar.svg') }}" class="img-fluid" alt="Compressed file" style="width: 24px; height: 24px;">
                    @elseif (in_array($fileExtension, ['mp4', 'mov', 'avi']))
                        <img src="{{ asset('dist/img/icons/video.svg') }}" class="img-fluid" alt="Video file" style="width: 24px; height: 24px;">
                    @elseif (in_array($fileExtension, ['mp3']))
                        <img src="{{ asset('dist/img/icons/mp3.svg') }}" class="img-fluid" alt="MP3 file" style="width: 24px; height: 24px;">
                    @elseif (in_array($fileExtension, ['wav']))
                        <img src="{{ asset('dist/img/icons/wav.svg') }}" class="img-fluid" alt="WAV file" style="width: 24px; height: 24px;">
                    @elseif (in_array($fileExtension, ['bmp']))
                        <img src="{{ asset('dist/img/icons/bits.svg') }}" class="img-fluid" alt="BMP file" style="width: 24px; height: 24px;">
                    @elseif (in_array($fileExtension, ['pptx']))
                        <img src="{{ asset('dist/img/icons/powerpoint.svg') }}" class="img-fluid" alt="Powerpoint file" style="width: 24px; height: 24px;">
                    @else
                        <img src="{{ asset('dist/img/icons/other.svg') }}" class="img-fluid" alt="Other file" style="width: 24px; height: 24px;">
                    @endif

                    <!-- Nombre del archivo -->
                    <div class="ml-3 flex-grow-1">
                        <p class="mb-1 archivo-nombre">{{ $archivo->nombre }}</p>
                    </div>

                    <!-- Botones de acción -->
                    <div class="ml-auto">
                        <!-- Ver -->
                        @if ($archivo->permisos->isNotEmpty() && $archivo->permisos[0]->ver)
                            <a href="{{ route('archivo.show', $archivo->id) }}" class="btn btn-info btn-sm" data-toggle="tooltip" title="Ver" target="_blank">
                                <i class="bi bi-eye"></i>
                            </a>
                        @else
                            <button class="btn btn-info btn-sm" disabled title="No tienes permisos para ver. Dile al administrador que te los otorgue.">
                                <i class="bi bi-eye" style="color: gray; opacity: 0.5; pointer-events: none;"></i>
                            </button>
                        @endif

                        <!-- Descargar -->
                        @if ($archivo->permisos->isNotEmpty() && $archivo->permisos[0]->descargar)
                            <a href="{{ route('archivo.download', $archivo->id) }}" class="btn btn-success btn-sm" data-toggle="tooltip" title="Descargar">
                                <i class="bi bi-download"></i>
                            </a>
                        @else
                            <button class="btn btn-success btn-sm" disabled title="No tienes permisos para descargar. Dile al administrador que te los otorgue.">
                                <i class="bi bi-download" style="color: gray; opacity: 0.5; pointer-events: none;"></i>
                            </button>
                        @endif

                    

                        <!-- Eliminar -->
                        @if ($archivo->permisos->isNotEmpty() && $archivo->permisos[0]->eliminar)
                            <form action="{{ route('archivo.mover-a-papelera', $archivo->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @else
                            <button class="btn btn-danger btn-sm" disabled title="No tienes permisos para eliminar. Dile al administrador que te los otorgue.">
                                <i class="bi bi-trash" style="color: gray; opacity: 0.5; pointer-events: none;"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p>No hay archivos en esta carpeta.</p>
    @endif
</div>
<script>
 // Seleccionamos el input y la lista de archivos
const input = document.getElementById('searchInput');
const list = document.querySelector('.list-group');
const items = list.getElementsByClassName('list-group-item');

// Función para filtrar los archivos según el texto ingresado
function filterFiles() {
    const filter = input.value.trim().toUpperCase(); // Obtener el filtro y convertir a mayúsculas
    console.log("Filtro ingresado:", filter); // Verifica que filtro estamos usando

    // Iteramos sobre todos los elementos de la lista
    for (let item of items) {
        const archivoNombreElement = item.querySelector('.archivo-nombre'); // Buscamos el nombre del archivo

        if (archivoNombreElement) {
            const txtValue = archivoNombreElement.textContent || archivoNombreElement.innerText;
            console.log("Texto del archivo:", txtValue); // Verifica qué texto estamos comparando

            // Comparamos el texto con el filtro
            if (txtValue.toUpperCase().includes(filter)) {
                item.classList.remove('hidden'); // Si coincide, mostramos el archivo
            } else {
                item.classList.add('hidden'); // Si no coincide, lo ocultamos
            }
        }
    }
}

// Evento de entrada para escuchar lo que el usuario escribe
input.addEventListener('input', filterFiles);
</script>
@endsection

@push('scripts')
    <script>
        document.getElementById('fileInput').addEventListener('change', function(event) {
            const fileList = document.getElementById('fileList');
            fileList.innerHTML = ''; // Clear existing file list

            for (const file of event.target.files) {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-list-item';
                fileItem.innerHTML = `<i class="bi bi-file-earmark"></i> ${file.name}`;
                fileList.appendChild(fileItem);
            }
        });
    </script>

@endpush
