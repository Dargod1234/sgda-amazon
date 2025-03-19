@php
    $userPermission = $folder->permisos->firstWhere('user_id', $user->id);
    $usuarios = \App\Models\User::where('id', '!=', Auth::id())->get();
@endphp
<style>
    .dropdown-menu .fas {
        font-size: 1.33rem; /* Aumenta el tamaño de los iconos */
    }
        
    .dropdown-menu .fa-solid {
        font-size: 1.33rem; /* Aumenta el tamaño de los iconos */
    }

    .icon-create-folder {
        color: #43A047 !important;  /* Verde brillante (más fresco) */
    }

    .icon-create-file {
        color: #2196F3 !important;   /* Azul profesional */
    }

    .icon-share {
        color: #0288D1 !important;  /* Azul oscuro */
    }

    .icon-upload {
        color: #FF9800 !important;  /* Naranja brillante */
    }

    .icon-edit {
        color: #757575 !important;  /* Gris oscuro neutral */
    }

    .icon-archive {
        color: #607D8B !important;  /* Gris azulado más suave */
    }

    .icon-delete {
        color: #D32F2F !important;  /* Rojo más serio y firme */
    }
    
    .icon-move {
        color: #2979FF !important; /* Azul brillante para indicar acción */
    }
    

</style>
@if ($userPermission && $userPermission->ver) <!-- Solo imprimir si tiene permiso para ver -->
    <div class="folder-item">
        <div class="d-flex justify-content-between align-items-center">
              <a class="folder-toggle text-decoration-none" data-folder-id="{{ $folder->id }}" style="margin-left: 15px;" role="button">
                <i class="bi bi-folder-fill me-2" style="color: {{ $folder->color ?? '#ebc034' }}; font-size:20px;"></i>
                {{ $folder->nombre }}
            </a>
            <div class="d-flex align-items-center gap-2">
                  <!-- Contador de archivos -->
                <span class="badge bg-secondary rounded-pill" style="font-size: 0.75rem;">
                   
                    {{ $folder->archivos->count() }}
                </span>
                <!-- Contador de subcarpetas -->
                <span class="badge rounded-pill" style="background-color: #F1D592; font-size: 0.75rem;">
                 
                    {{ $folder->carpetasHijas->count() }}
                </span>
                
              
            </div>
            <div class="dropdown ml-2">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle dropright" type="button" 
                        id="dropdownMenuButton{{ $folder->id }}" data-toggle="dropdown" 
                        aria-haspopup="true" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#5f6368"><path d="M263.79-408Q234-408 213-429.21t-21-51Q192-510 213.21-531t51-21Q294-552 315-530.79t21 51Q336-450 314.79-429t-51 21Zm216 0Q450-408 429-429.21t-21-51Q408-510 429.21-531t51-21Q510-552 531-530.79t21 51Q552-450 530.79-429t-51 21Zm216 0Q666-408 645-429.21t-21-51Q624-510 645.21-531t51-21Q726-552 747-530.79t21 51Q768-450 746.79-429t-51 21Z"/></svg>
                </button>
               <div class="dropdown-menu p-2" 
                 aria-labelledby="dropdownMenuButton{{ $folder->id }}" 
                 style="min-width: 200px; max-height: 300px; overflow-y: auto;">
                    <!-- Crear -->
                    <div class="dropdown-header text-primary font-weight-bold">Acciones de creación</div>
                    <a class="dropdown-item d-flex align-items-center" href="#" data-toggle="modal" data-target="#crearCarpetaModal-{{ $folder->id }}">
                        <i class="fas fa-folder-plus icon-create-folder mr-2"></i> Crear Carpeta
                    </a>
                    @if (Auth::user()->role == 'admin' || Auth::user()->role == 'moderator') 
                        <a class="dropdown-item d-flex align-items-center" href="#" data-toggle="modal" data-target="#createFileModal2-{{ $folder->id }}">
                            <i class="fa-solid fa-file-medical icon-create-file mr-2"></i> Crear Archivos
                        </a>
                    @endif
                    <a class="dropdown-item d-flex align-items-center" href="#" data-toggle="modal" data-target="#crearArchivoModal-{{ $folder->id }}">
                        <i class="fas fa-file-upload text-success mr-2"></i> Subir Archivo
                    </a>
                    <div class="dropdown-divider"></div>
                    @if (Auth::user()->role == 'admin' || Auth::user()->role == 'moderator')    
                        <!-- Compartir -->
                        <div class="dropdown-header text-info font-weight-bold">Acciones de compartir</div>
                        <a class="dropdown-item d-flex align-items-center" href="#" data-toggle="modal" data-target="#compartirContenidoModal-{{ $folder->id }}">
                            <i class="fas fa-share-alt icon-share mr-2"></i> Compartir Contenido
                        </a>
                        <a class="dropdown-item d-flex align-items-center" href="#" data-toggle="modal" data-target="#subirComprimidoArchivoModal-{{ $folder->id }}">
                            <i class="fas fa-cloud-upload-alt icon-upload mr-2"></i> Subir Comprimido
                        </a>
                        
                        <a class="dropdown-item d-flex align-items-center" href="#" data-toggle="modal" data-target="#trasladarCarpetaModal-{{ $folder->id }}">
                            <i class="fa-solid fa-up-down-left-right icon-move mr-2"></i> Trasladar Carpeta
                        </a>
                     
                        <div class="dropdown-divider"></div>
                        
                        <!-- Modificar -->
                        <div class="dropdown-header text-secondary font-weight-bold">Acciones de modificación</div>
                        <a class="dropdown-item d-flex align-items-center" href="#" data-toggle="modal" data-target="#renameModal-{{ $folder->id }}">
                            <i class="fas fa-edit icon-edit mr-2"></i> Editar
                        </a>
                        <a class="dropdown-item d-flex align-items-center" href="#" data-toggle="modal" data-target="#archivarModal-{{ $folder->id }}">
                            <i class="fas fa-archive icon-archive mr-2"></i> Archivar
                        </a>
                         <div class="dropdown-divider"></div>
                       
                        <!-- Eliminar -->
                        
                        <a class="dropdown-item d-flex align-items-center text-danger" href="#" data-toggle="modal" data-target="#deleteModal-{{ $folder->id }}">
                            <i class="fas fa-trash-alt icon-delete mr-2"></i> Eliminar
                        </a>
              
                        <a href="{{ route('file-system.download', ['folderId' => $folder->id]) }}" class="dropdown-item d-flex align-items-center text-danger">
                            <i class="fas fa-download mr-2"></i> Descargar Contenido
                        </a>
                        <a class="dropdown-item d-flex align-items-center text-primary" href="#" data-toggle="modal" data-target="#folderInfoModal-{{ $folder->id }}">
                            <i class="fas fa-info-circle icon-info mr-2"></i> Ver Información
                        </a>
                @endif
                    
                </div>

            </div>
        </div>
  
        <!-- Colapsar carpetas hijas -->
        <div class="collapse ms-3" id="folder-{{ $folder->id }}">
            @if ($folder->carpetasHijas->count() > 0)
                <ul class="list-unstyled mb-0">
                    @foreach ($folder->carpetasHijas as $subfolder)
                        <li>
                            @include('partials.folder-tree', ['folder' => $subfolder]) <!-- Llamada recursiva -->
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
         
                        <div class="modal fade" id="renameModal-{{ $folder->id }}" tabindex="-1" role="dialog" aria-labelledby="renameModalLabel{{ $folder->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="renameModalLabel{{ $folder->id }}">Editar Carpeta</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="{{ route('file-system.update_folder') }}"  method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <input type="hidden" value="{{ $folder->id }}" id="id" name="id">
                                            <div class="form-group">
                                                <label for="nombre{{ $folder->id }}">Nuevo nombre</label>
                                                <input type="text" class="form-control" id="nombre{{ $folder->id }}" name="nombre" value="{{ $folder->nombre }}" required>
                                            </div>
                                                                <div class="mb-3">
                                                <label for="color" class="form-label">Color de la Carpeta</label>
                                                <input type="color" class="form-control" id="color" name="color" value="#34B3EB">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    
                        <div class="modal fade" id="deleteModal-{{ $folder->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel{{ $folder->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteModalLabel{{ $folder->id }}">Eliminar Carpeta</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    <div class="modal-body">
                                            ¿Estás seguro de que deseas eliminar la carpeta <strong>{{ $folder->nombre }}</strong>? Esta acción no se puede deshacer.
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                        <form action="{{ route('file-system.destroy', $folder->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="modal fade" id="archivarModal-{{ $folder->id }}" tabindex="-1" role="dialog" aria-labelledby="archivarModalLabel{{ $folder->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="archivarModalLabel{{ $folder->id }}">Archivar Carpeta(Dejar fuera de serie)</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    <div class="modal-body">
                                            ¿Estás seguro de que deseas Archivar la carpeta <strong>{{ $folder->nombre }}</strong>?.
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                        <form action="{{ route('file-system.absoleta', $folder->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger">Archivar (Dejar Fuera de Serie)</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- MODALES PARTE 2 Carpeta -->
                        
                        <!-- Modal Crear Carpeta -->
                       <div class="modal fade" id="crearCarpetaModal-{{ $folder->id }}" tabindex="-1" role="dialog" aria-labelledby="crearCarpetaModalLabel{{ $folder->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="crearCarpetaModalLabel{{ $folder->id }}">Crear Carpeta</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="{{ route('file-system.store_subfolder') }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <input type="hidden" name="carpeta_padre_id" value="{{ $folder->id }}">
                                            <div class="form-group">
                                                <label for="nombreCarpeta{{ $folder->id }}">Nombre de la carpeta</label>
                                                <input type="text" class="form-control" id="nombreCarpeta{{ $folder->id }}" name="nombre" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="colorCarpeta{{ $folder->id }}">Color de la carpeta</label>
                                                <input type="color" class="form-control" id="colorCarpeta{{ $folder->id }}" name="color" value="#34B3EB">
                                            </div>
                                            <div class="form-group">
                                                <label>Carpeta padre</label>
                                                <input type="text" class="form-control" value="{{ $folder->nombre }}" disabled>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Crear</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        
                            <div class="modal fade" id="crearArchivoModal-{{ $folder->id }}" tabindex="-1" role="dialog" aria-labelledby="crearArchivoModalLabel{{ $folder->id }}" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="crearArchivoModalLabel{{ $folder->id }}">Subir Archivos</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="{{ route('file-system.upload_file') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-body">
                                                <input type="hidden" name="folder_id" value="{{ $folder->id }}">
                                                <div class="form-group">
                                                    <label for="archivos{{ $folder->id }}">Seleccionar archivos</label>
                                                    <input type="file" class="form-control" id="archivos{{ $folder->id }}" name="file[]" multiple required>
                                                    <small class="form-text text-muted">Puedes subir múltiples archivos. Tamaño máximo por archivo: 100MB.</small>
                                                </div>
                                                <div class="form-group">
                                                    <label>Carpeta de destino</label>
                                                    <input type="text" class="form-control" value="{{ $folder->nombre }}" disabled>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary">Subir Archivos</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>


                        <!-- Modal Compartir Contenido -->
                        <div class="modal fade" id="compartirContenidoModal-{{ $folder->id }}" tabindex="-1" role="dialog" aria-labelledby="compartirContenidoModalLabel{{ $folder->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="compartirContenidoModalLabel{{ $folder->id }}">Compartir Contenido</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                     <form action="{{ route('folders.share') }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <input type="hidden" value="{{ $folder->id }}" id="folder_id" name="folder_id">
                                            <div class="form-group">
                                                    <label>Carpeta a Compartir</label>
                                                    <input type="text" class="form-control" value="{{ $folder->nombre }}" disabled>
                                            </div>
                                            <!-- Seleccionar Usuario -->
                                            <div class="form-group">
                                                <label for="user_id" class="form-label">Seleccionar Usuario</label>
                                                <select class="form-control" id="user_id" name="user_id" required>
                                                    <option value="">Seleccione un usuario</option>
                                                    @foreach ($usuarios as $usuario)
                                                        <option value="{{ $usuario->id }}">{{ $usuario->name }} ({{ $usuario->email }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <!-- Seleccionar Permisos -->
                                            <div class="form-group">
                                                <label class="form-label">Permisos</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1" id="perm_ver" name="permisos[ver]">
                                                    <label class="form-check-label" for="perm_ver">Ver</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1" id="perm_descargar" name="permisos[descargar]">
                                                    <label class="form-check-label" for="perm_descargar">Descargar</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1" id="perm_editar" name="permisos[editar]">
                                                    <label class="form-check-label" for="perm_editar">Editar</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1" id="perm_eliminar" name="permisos[eliminar]">
                                                    <label class="form-check-label" for="perm_eliminar">Eliminar</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Compartir</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                  
                        
                        <!-- Modal Subir Comprimido -->
                        <div class="modal fade" id="subirComprimidoArchivoModal-{{ $folder->id }}" tabindex="-1" role="dialog" aria-labelledby="subirComprimidoArchivoModalLabel{{ $folder->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="subirComprimidoArchivoModalLabel{{ $folder->id }}">Subir Archivo Comprimido</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="{{ route('upload.zip') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="form-group">
                                                    <label>Carpeta a Compartir</label>
                                                    <input type="text" class="form-control" value="{{ $folder->nombre }}" disabled>
                                                    <input type="hidden" name="folder_id" value="{{ $folder->id }}"> <!-- Campo oculto para enviar el ID de la carpeta -->
                                            </div>
                                            <div class="mb-4">
                                                <label for="zipfile" class="form-label">Selecciona un archivo .zip:</label>
                                                <input type="file" name="zipfile" id="zipfile" class="form-control" accept=".zip" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Subir</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        
<div class="modal fade" id="createFileModal2-{{ $folder->id }}" tabindex="-1" aria-labelledby="createFileModalLabel2" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('file-system.create_file') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createFileModalLabel2">Crear Nuevo Archivo</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <!-- Carpeta Destino -->
                    <div class="mb-3">
                        <label>Carpeta Destino</label>
                        <input type="text" class="form-control" value="{{ $folder->nombre }}" disabled>
                        <input type="hidden" name="folder_id" value="{{ $folder->id }}"> <!-- Campo oculto para enviar el ID de la carpeta -->
                    </div>
                    <!-- Nombre del Archivo -->
                    <div class="mb-3">
                        <label for="fileName" class="form-label">Nombre del Archivo</label>
                        <input type="text" class="form-control" id="fileName" name="file_name" required>
                    </div>
                    <!-- Tipo de Archivo -->
                    <div class="mb-3">
                        <label for="fileType" class="form-label">Tipo de Archivo</label>
                        <select class="form-select" id="fileType" name="file_type" required>
                            <option value="">Seleccione un tipo de archivo</option>
                            <option value="docx">Word</option>
                            <option value="xlsx">Excel</option>
                            <option value="pptx">Power Point</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Crear Archivo</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="folderInfoModal-{{ $folder->id }}" tabindex="-1" aria-labelledby="folderInfoModalLabel-{{ $folder->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="folderInfoModalLabel-{{ $folder->id }}">Información de la Carpeta</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- Información del Folder -->
                <div class="mb-3">
                    <strong>Nombre del Folder:</strong>
                    <p>{{ $folder->nombre }}</p>
                </div>
                <div class="mb-3">
                    <strong>Descripción:</strong>
                    <p>{{ $folder->descripcion ?? 'No disponible' }}</p>
                </div>
                <div class="mb-3">
                    <strong>ID del Folder en OneDrive:</strong>
                    <p>{{ $folder->google_drive_folder_id }}</p>
                </div>
                <div class="mb-3">
                    <strong>Creador:</strong>
                    <p>{{ $folder->user->name ?? 'No disponible' }}</p>
                </div>
                <div class="mb-3">
                    <strong>Fecha de Creación:</strong>
                    <p>{{ $folder->created_at->format('d-m-Y H:i:s') }}</p>
                </div>
                <div class="mb-3">
                    <strong>Última Modificación:</strong>
                    <p>{{ $folder->updated_at->format('d-m-Y H:i:s') }}</p>
                </div>
                <!-- Información adicional de los archivos (si lo deseas) -->
                <div class="mb-3">
                    <strong>Archivos Asociados:</strong>
                    <ul>
                        @foreach($folder->archivos as $archivo)
                            <li>{{ $archivo->nombre }} - {{ $archivo->mime_type }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="trasladarCarpetaModal-{{ $folder->id }}" tabindex="-1" role="dialog" aria-labelledby="trasladarCarpetaModalLabel-{{ $folder->id }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="trasladarCarpetaModalLabel-{{ $folder->id }}">Trasladar Carpeta</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="trasladarCarpetaForm-{{ $folder->id }}" action="{{ route('file-system.move-carpeta') }}" method="POST">
                @csrf
                <input type="hidden" name="folderId" value="{{ $folder->id }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="carpetaDestino-{{ $folder->id }}">Selecciona la carpeta de destino:</label>
                        <select id="carpetaDestino-{{ $folder->id }}" name="carpetaDestino" class="form-control">
                            <option value="">-- Seleccione una carpeta --</option>
                             @foreach ($folders as $folder)
                                @include('partials.folder-option', ['folder' => $folder, 'level' => 0])
                             @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Trasladar</button>
                </div>
            </form>
        </div>
    </div>
</div>
                        
                                            
@endif



