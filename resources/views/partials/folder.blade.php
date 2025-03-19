<meta charset="UTF-8">
<style>
    .modal-header.bg-info {
        background-color: #17a2b8;
        color: #fff;
        border-bottom: 2px solid #0d6efd;
    }

    .list-group-item {
        font-size: 14px;
        padding: 10px 15px;
    }

    .modal-footer {
        border-top: 1px solid #ddd;
    }
</style>

<div class="d-flex justify-content-between align-items-center">
    <!-- Checkbox general para seleccionar todo -->
    <div class="form-check me-3">
        <input type="checkbox" class="form-check-input" id="select-all-{{ $folder->id }}" onclick="toggleCheckboxes({{ $folder->id }})">
        <label class="form-check-label" for="select-all-{{ $folder->id }}">Seleccionar Todo</label>
    </div>

    <!-- Contenedor para el nombre de la carpeta -->
    <a class="folder-toggle text-decoration-none" style="margin-left: 15px;" data-folder-id="{{ $folder->id }}" role="button">
        <i class="bi bi-folder"></i> {{ $folder->nombre }}
    </a>
    <div class="flex-grow-1 mx-2">
        <hr style="border: 1px solid #b4b4b4; margin: 0;" />
    </div>
    <!-- Contenedor para los checkboxes de permisos -->
    <div class="d-flex align-items-center ms-3">
        @php
            $permisoCarpeta = $folder->permisos->where('user_id', $user->id)->first();
        @endphp

        <div class="form-check form-check-inline d-flex align-items-center">
            <input type="checkbox" class="form-check-input" id="check-view-folder-{{ $folder->id }}"
                name="permisos[carpeta][{{ $folder->id }}][ver]"
                {{ $permisoCarpeta && $permisoCarpeta->ver ? 'checked' : '' }}>
            <label class="form-check-label" for="check-view-folder-{{ $folder->id }}">
                <i class="bi bi-eye" style="font-size: 1.33em;"></i>
            </label>
        </div>

        <!-- Bot車n para mostrar la informaci車n de la carpeta -->
        <div class="d-flex align-items-center ms-3">
            <button class="btn btn-sm btn-info" type="button" data-bs-toggle="modal" data-bs-target="#infoModal-{{ $folder->id }}">
                <i class="bi bi-info-circle"></i>
            </button>
        </div>
    </div>
</div>

<!-- Modal para la carpeta -->
<div class="modal fade" id="infoModal-{{ $folder->id }}" tabindex="-1" aria-labelledby="infoModalLabel-{{ $folder->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="infoModalLabel-{{ $folder->id }}">
                    Informacion de la Carpeta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Nombre:</strong>
                    <span>{{ $folder->nombre }}</span>
                </div>
                <div class="mb-3">
                    <strong>Creador:</strong>
                    <span>{{ $folder->user->name }}</span>
                </div>
                <div>
                    <strong>Permisos:</strong>
                    <ul class="list-group">
                        @foreach ($folder->usuariosConPermiso as $usuario)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $usuario->name }}
                                <span>
                                    Ver: {{ $usuario->pivot->ver ? 'Si' : 'No' }}, 
                                    Descargar: {{ $usuario->pivot->descargar ? 'Si' : 'No' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Colapsable para carpetas hijas y archivos -->
<div class="collapse ms-3" id="folder-{{ $folder->id }}">
    {{-- Subcarpetas --}}
    @if ($folder->carpetasHijas->count() > 0)
    <ul class="list-unstyled mb-0">
        @foreach ($folder->carpetasHijas as $subfolder)
            <li>
                @include('partials.folder', ['folder' => $subfolder])
            </li>
        @endforeach
    </ul>

    @endif

    {{-- Archivos en la carpeta --}}
    @if ($folder->archivos->count() > 0)
        <ul class="list-unstyled mb-0">
            @foreach ($folder->archivos as $archivo)
                <li class="d-flex align-items-center w-100">
                    <!-- Contenedor para el nombre del archivo -->
                    <div class="d-flex align-items-center me-3">
                        <i class="bi bi-file-earmark"></i>
                        <span class="ms-2">{{ $archivo->nombre }}</span>
                    </div>

                    <!-- L赤nea que conecta el archivo con los permisos -->
                    <div class="flex-grow-1 mx-2">
                        <hr style="border: 1px solid #b4b4b4; margin: 0;" />
                    </div>

                    <!-- Contenedor para los checkboxes de permisos -->
                    <div class="d-flex align-items-center ms-3">
                        @php
                            $permisoArchivo = $archivo->permisos->where('user_id', $user->id)->first();
                        @endphp

                        <div class="form-check form-check-inline d-flex align-items-center">
                            <input type="checkbox" class="form-check-input" id="check-download-{{ $archivo->id }}"
                                name="permisos[file][{{ $archivo->id }}][descargar]"
                                {{ $permisoArchivo && $permisoArchivo->descargar ? 'checked' : '' }}>
                            <label class="form-check-label" for="check-download-{{ $archivo->id }}">
                                <i class="bi bi-download" style="font-size: 1.33em;"></i>
                            </label>
                        </div>
                        <div class="form-check form-check-inline d-flex align-items-center">
                            <input type="checkbox" class="form-check-input" id="check-delete-{{ $archivo->id }}"
                                name="permisos[file][{{ $archivo->id }}][eliminar]"
                                {{ $permisoArchivo && $permisoArchivo->eliminar ? 'checked' : '' }}>
                            <label class="form-check-label" for="check-delete-{{ $archivo->id }}">
                                <i class="bi bi-trash" style="font-size: 1.33em;"></i>
                            </label>
                        </div>
                        <div class="form-check form-check-inline d-flex align-items-center">
                            <input type="checkbox" class="form-check-input" id="check-view-{{ $archivo->id }}"
                                name="permisos[file][{{ $archivo->id }}][ver]"
                                {{ $permisoArchivo && $permisoArchivo->ver ? 'checked' : '' }}>
                            <label class="form-check-label" for="check-view-{{ $archivo->id }}">
                                <i class="bi bi-eye" style="font-size: 1.33em;"></i>
                            </label>
                        </div>
                        <!-- Checkbox para la opci車n "editar" en archivos -->
                        <div class="form-check form-check-inline d-flex align-items-center">
                            <input type="checkbox" class="form-check-input" id="check-edit-{{ $archivo->id }}"
                                name="permisos[file][{{ $archivo->id }}][editar]"
                                {{ $permisoArchivo && $permisoArchivo->editar ? 'checked' : '' }}>
                            <label class="form-check-label" for="check-edit-{{ $archivo->id }}">
                                <i class="bi bi-pencil" style="font-size: 1.33em;"></i>
                            </label>
                        </div>

                        <!-- Bot車n para mostrar la informaci車n del archivo -->
                        <div class="d-flex align-items-center ms-3">
                            <button class="btn btn-sm btn-info" type="button" data-bs-toggle="modal" data-bs-target="#infoModalArchivo-{{ $archivo->id }}">
                                <i class="bi bi-info-circle"></i>
                            </button>
                        </div>
                    </div>
                </li>

                <!-- Modal para el archivo -->
                <div class="modal fade" id="infoModalArchivo-{{ $archivo->id }}" tabindex="-1" aria-labelledby="infoModalArchivoLabel-{{ $archivo->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <!-- Encabezado del modal -->
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title" id="infoModalArchivoLabel-{{ $archivo->id }}">
                                    Informacion del Archivo
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <!-- Cuerpo del modal -->
                            <div class="modal-body">
                                <div class="mb-3">
                                    <strong>Nombre:</strong>
                                    <span>{{ $archivo->nombre }}</span>
                                </div>
                                <div class="mb-3">
                                    <strong>Propietario:</strong>
                                    <span>{{ $archivo->user->name }}</span>
                                </div>
                                <div>
                                    <strong>Permisos:</strong>
                                    <ul class="list-group">
                                        @foreach ($archivo->permisos as $permiso)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                {{ $permiso->user->name }}
                                                <span>
                                                    Ver: {{ $permiso->ver ? 'Si' : 'No' }}, 
                                                    Descargar: {{ $permiso->descargar ? 'Si' : 'No' }}, 
                                                    Editar: {{ $permiso->editar ? 'Si' : 'No' }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <!-- Pie del modal -->
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

            @endforeach
        </ul>
    @endif
</div>

<script>
    function toggleCheckboxes(folderId) {
        // Obtener el estado del checkbox "Seleccionar Todo"
        const selectAll = document.getElementById(`select-all-${folderId}`).checked;

        // Obtener todos los checkboxes dentro de esta carpeta
        const checkboxes = document.querySelectorAll(`#folder-${folderId} input[type="checkbox"]`);

        // Cambiar el estado de todos los checkboxes seg迆n el checkbox general
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll;
        });
    }
</script>
