@extends('layouts.admin')

@section('content')
<style>
    .folder-list {
        min-height: 35vh;
        max-height: 65vh; /* Altura dinámica */
        overflow-y: auto;
        border: 1px solid #ddd;
        background-color: #f8f9fa;
        padding: 5px;
        border-radius: 5px;
    }
    .table-responsive {
        overflow-x: auto; /* Permitir desplazamiento horizontal */
        white-space: nowrap; /* Prevenir el salto de líneas en las filas */
    }
    

    .columna-carpeta .btn {
        width: 100%; /* Botones a pantalla completa en móviles */
        margin-bottom: 10px;
        background-color : #00005c !important;
    }

    @media (min-width: 768px) {
        .btn {
            width: auto; /* Botones normales en pantallas medianas y grandes */
            margin-bottom: 0;
        }
    }
    @media (min-width: 768px) and (max-width: 991px) {
    .folder-list {
        width: 100%;
        margin: 0 auto;
    }

    

    .folder-container {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .columna-carpeta   {
        display: flex;
        width: 100%;
        justify-content: center;
        align-items: center;
        align-content: flex-start;
        flex-wrap: wrap
    }
    .columna-tabla {
        display: flex;
        width: 100%;
        justify-content: center;
        align-items: center;
        align-content: flex-start;
        flex-wrap: wrap
    }
</style>
@php
$usuarios = \App\Models\User::where('id', '!=', Auth::id())->get();
@endphp

<div class="container py-4">
    <div class="row">
        <!-- Encabezado -->
        <div class="col-12 mb-4">
            <div class="header bg-primary text-white text-center p-3 rounded">
                <h2>IGAMOCOL S.A.S.©</h2>
            </div>
        </div>

  
        <!-- Columna de Carpetas -->
        <div class="col-md-3 mb-4 columna-carpeta">
             @if (Auth::user()->role == 'admin' || Auth::user()->role == 'moderator')    
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newFolderModal">+ Nueva Carpeta</button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#zipModal">
                    <i class="bi bi-file-earmark-zip me-2"></i> Subir ZIP
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#shareFolderModal">
                    <i class="bi bi-share me-2"></i> Compartir
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFileModal">
                    <i class="bi bi-file-earmark-plus"></i> Crear Archivo
                </button>
            @endif

            <div class="folder-list mt-3">
                @foreach ($folders as $folder)
                    @php $userPermission = $folder->permisos->firstWhere('user_id', $user->id); @endphp
                    @if ($userPermission && $userPermission->ver)
                        <div class="folder-item">
                            @include('partials.folder-tree', ['folder' => $folder])
                        </div>
                    @endif
                @endforeach

                <div class="mt-3 d-flex justify-content-center">
                    {{ $folders->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>

        <!-- Columna de Archivos -->
        <div class="col-md-9 columna-tabla">
            <div class="d-flex justify-content-between align-items-center mb-3" style="width:100%">
                <h4 id="folder-title" class="m-0">Título de la Carpeta</h4>
                <button class="btn btn-primary btn-lg shadow" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bi bi-cloud-upload"></i> Subir Archivos
                </button>
            </div>

              
            <div class="table-responsive">
                <table class="table table-striped align-middle text-center system-table" id="file-table">
                    <thead>
                        <tr>
                            <th >Icono</th>
                            <th >Nombre</th>
                            <th >Carpeta</th>
                            <th >Creado</th>
                            <th >Actualizado</th>
                            <th >Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($folder) && $folder->archivos->isNotEmpty())
                        
                            @foreach ($folder->archivos as $file)
                                <!-- Renderizar archivos aquí -->
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center">Seleccione una carpeta para ver sus archivos.</td>
                            </tr>
                        @endif
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <form id="search-form">
        <div class="input-group">
            <input type="text" class="form-control" name="query" placeholder="Buscar archivos o carpetas..." required>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> Buscar
            </button>
        </div>
    </form>
</div>
@endsection



<div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="renameForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="renameModalLabel">Renombrar Archivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="renameArchivoId" name="archivo_id">
                    <div class="mb-3">
                        <label for="newName" class="form-label">Nuevo Nombre</label>
                        <input type="text" class="form-control" id="newName" name="new_name" required>
                        <div class="invalid-feedback">
                            Por favor, ingresa un nuevo nombre válido.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Renombrar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Subir Archivos</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('file-system.upload_file') }}" id="uploadForm" method="POST" enctype="multipart/form-data" class="">
                @csrf
                <div class="mb-4">
                    <label for="folderSelect" class="form-label">Seleccionar Carpeta</label>
                    <select class="form-select" id="folderSelect" name="folder_id" required>
                        <option value="">Seleccione una carpeta</option>
                        @foreach ($folders as $folder)
                            @include('partials.folder-option', ['folder' => $folder, 'level' => 0])
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="fileInput" class="form-label">Seleccionar Archivos</label>
                    <input type="file" id="file" name="file[]" class="form-control" multiple required>
                    <small class="form-text text-muted">Puedes seleccionar múltiples archivos.</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Subir</button>
</form>

            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="newFolderModal" tabindex="-1" aria-labelledby="newFolderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newFolderModalLabel">Crear Nueva Carpeta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('mi_unidad.store') }}" method="POST">
                    @csrf 
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre de la Carpeta</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="color" class="form-label">Color de la Carpeta</label>
                        <input type="color" class="form-control" id="color" name="color" value="#34B3EB">
                    </div>
                    <div class="mb-3">
                        <label for="parentFolderSelect" class="form-label">Carpeta Padre</label>
                        <select class="form-select" id="parentFolderSelect" name="carpeta_padre_id">
                            <option value="">Seleccione una carpeta</option>
                            @foreach ($folders as $folder)
                                @include('partials.folder-option', ['folder' => $folder, 'level' => 0])
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Carpeta</button>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="shareFolderModal" tabindex="-1" role="dialog" aria-labelledby="shareFolderModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('folders.share') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="shareFolderModalLabel">Compartir Carpeta</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                 
                    <div class="form-group">
                        <label for="folder_id" class="form-label">Seleccionar Carpeta</label>
                        <select class="form-control" id="folder_id" name="folder_id" required>
                            <option value="">Seleccione una carpeta</option>
                            @foreach ($folders as $folder)
                                @include('partials.folder-option', ['folder' => $folder, 'level' => 0])
                            @endforeach
                        </select>
                    </div>

         
                    <div class="form-group">
                        <label for="user_id" class="form-label">Seleccionar Usuario</label>
                        <select class="form-control" id="user_id" name="user_id" required>
                            <option value="">Seleccione un usuario</option>
                            @foreach ($usuarios as $usuario)
                                <option value="{{ $usuario->id }}">{{ $usuario->name }} ({{ $usuario->email }})</option>
                            @endforeach
                        </select>
                    </div>

    
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
                    <button type="submit" class="btn btn-primary">Compartir</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>



<div class="modal fade" id="zipModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Subir y Procesar Archivo .zip</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('upload.zip') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                         <div class="mb-4">
                            <label for="folderSelect" class="form-label">Seleccionar Carpeta</label>
                            <select class="form-select" id="folderSelect" name="folder_id" required>
                                <option value="">Seleccione una carpeta</option>
                                @foreach ($folders as $folder)
                                    @include('partials.folder-option', ['folder' => $folder, 'level' => 0])
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="zipfile" class="form-label">Selecciona un archivo .zip:</label>
                            <input type="file" name="zipfile" id="zipfile" class="form-control" accept=".zip" required>
                        </div>
                        <div class="progress mb-4" style="height: 25px; display: none;" id="uploadProgressContainer">
                        <div class="progress-bar" role="progressbar" id="uploadProgressBar" style="width: 0%;"></div>
                    </div>
                        <button type="submit" class="btn btn-primary">Subir y Procesar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="createFileModal" tabindex="-1" aria-labelledby="createFileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('file-system.create_file') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createFileModalLabel">Crear Nuevo Archivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
     
                    <div class="mb-3">
                        <label for="fileName" class="form-label">Nombre del Archivo</label>
                        <input type="text" class="form-control" id="fileName" name="file_name" required>
                    </div>
          
                    <div class="mb-3">
                        <label for="fileType" class="form-label">Tipo de Archivo</label>
                        <select class="form-select" id="fileType" name="file_type" required>
                            <option value="">Seleccione un tipo de archivo</option>
                            <option value="docx">Word</option>
                            <option value="xlsx">Excel</option>
                            <option value="pptx">Power Point</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="folderSelectCreateFile" class="form-label">Seleccionar Carpeta</label>
                        <select class="form-select" id="folderSelectCreateFile" name="folder_id" required>
                            <option value="">Seleccione una carpeta</option>
                            @foreach ($folders as $folder)
                                @include('partials.folder-option', ['folder' => $folder, 'level' => 0])
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Crear Archivo</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="moverModal" tabindex="-1" role="dialog" aria-labelledby="moverModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moverModalLabel">Mover Archivo</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="moverForm" action="{{ route('file-system.move') }}" method="POST">
                @csrf
                <input type="hidden" id="archivoId" name="archivoId" value="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="carpetaDestino">Selecciona la carpeta de destino:</label>
                        <select id="carpetaDestino" name="carpetaDestino" class="form-control">
                            <option value="">-- Seleccione una carpeta --</option>
                             @foreach ($folders as $folder)
                                @include('partials.folder-option', ['folder' => $folder, 'level' => 0])
                            @endforeach
                            
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Mover</button>
                </div>
            </form>
        </div>
    </div>
</div>
