<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CarpetaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\UnidadController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\FolderPermissionController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\FileSystemController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ActivoController;

Auth::routes();

Route::get('/login/oneDrive', [GoogleAuthController::class, 'redirectToOneDrive'])->name('google.login');
Route::get('/callback', [GoogleAuthController::class, 'handleOneDriveCallback'])->name('google.callback');

Route::middleware('isAdmin')->group(function () {
    
    Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
        Route::get('clients/import', [ClientController::class, 'importForm'])->name('clients.import.form');
        Route::post('clients/import', [ClientController::class, 'import'])->name('clients.import');
        Route::get('clientes/{client}/activos', [ActivoController::class, 'index'])
             ->name('clients.assets.index');
        Route::get('activos/create', [ActivoController::class, 'create'])
             ->name('activos.create');
        Route::resource('clients', ClientController::class);
        Route::resource('activos', ActivoController::class);
    });
    
    Route::post('/upload-zip', [FiLeSystemController::class, 'uploadZip'])->name('upload.zip');
    // Rutas de administrador
    Route::get('/file-system', [FiLeSystemController::class, 'index'])->name('file-system.home');
    Route::get('/file-system/get-folders', [FiLeSystemController::class, 'getFolder'])->name('file-system.folders');
    Route::get('/file-system/{id}/files', [FileSystemController::class, 'getFiles'])->name('folders.files');
    Route::post('/file-system/create', [FileSystemController::class, 'createFile'])->name('file-system.create_file');
    Route::post('/file-system', [FileSystemController::class, 'compartir'])->name('folders.share');
    Route::put('/file-system/editar', [CarpetaController::class, 'update_folder'])->name('file-system.update_folder');
    Route::delete('/file-system/eliminar/carpeta/{id}', [CarpetaController::class, 'destroy'])->name('file-system.destroy');
    Route::post('/file-system/archivar/carpeta/{id}', [CarpetaController::class, 'marcarObsoleta'])->name('file-system.absoleta');
    Route::get('/file-system/search', [FileSystemController::class, 'search'])->name('file-system.search');
    Route::post('/file-system/create-subfolder', [CarpetaController::class, 'store_subfolder'])->name('file-system.store_subfolder');
    Route::post('/file-system/upload_file', [ArchivoController::class, 'upload_file'])->name('file-system.upload_file');
    Route::post('/file-system/mover-archivo', [FileSystemController::class, 'moverArchivo'])->name('file-system.move');
    Route::post('/file-system/mover-carpeta', [FileSystemController::class, 'moverCarpeta'])->name('file-system.move-carpeta');
    Route::get('/file-system/descargar/{folderId}', [CarpetaController::class, 'descargarContenidoCarpeta'])->name('file-system.download');

    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/users/login-history/{id}', [UserController::class, 'showLoginHistory'])->name('user.login-history');

    Route::get('/admin/users/{id}/permissions', [FolderPermissionController::class, 'index'])->name('user.permissions');
    Route::put('/admin/users/{carpeta_id}/permissions', [FolderPermissionController::class, 'guardarPermisos'])->name('guardar.permisos');
    Route::get('/admin/mi_unidad/buscar', [CarpetaController::class, 'buscar'])->name('mi_unidad.search');
    Route::get('/admin/users/{id}/unidad', [UnidadController::class, 'index'])->name('user.unit');
    Route::get('admin/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/admin/users/new', [UserController::class, 'create'])->name('users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/admin/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::get('/admin/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');



    Route::post('/archivo/{id}/rename', [ArchivoController::class, 'renameFile'])->name('archivo.rename');
    Route::get('/archivos/papelera', [ArchivoController::class, 'verPapelera'])->name('archivo.papelera');
    Route::get('/archivo/{id}', [ArchivoController::class, 'show'])->name('archivo.show');
    Route::delete('/archivos/{id}/mover-a-papelera', [ArchivoController::class, 'moverAPapelera'])->name('archivo.mover-a-papelera');
    Route::patch('/archivos/{id}/restaurar', [ArchivoController::class, 'restaurar'])->name('archivo.restaurar');
    Route::delete('/archivos/{id}/eliminar-permanentemente', [ArchivoController::class, 'eliminarPermanentemente'])->name('archivo.eliminar-permanentemente');
    
    Route::patch('/carpeta/restaurar/{id}', [CarpetaController::class, 'restaurar'])->name('carpeta.restaurar');
    Route::delete('/carpeta/eliminar-permanentemente/{id}', [CarpetaController::class, 'eliminarPermanente'])->name('carpeta.eliminar-permanentemente');
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    // Ruta para mostrar el historial de un archivo específico
    Route::get('/archivos/{fileId}/actividad', [ActivityLogController::class, 'showFileActivity'])->name('files.activity');
    // Ruta para mostrar el historial de todas las actividades
    Route::get('/actividad', [ActivityLogController::class, 'index'])->name('activities.index');
    Route::post('/admin/mi_unidad/upload', [ArchivoController::class, 'upload_file'])->name('archivo.upload_file');
    Route::post('/carpetas/{id}/marcar-obsoleta', [CarpetaController::class, 'marcarObsoleta'])->name('carpetas.obsoleta');
    Route::post('/carpetas/{id}/restaurar', [CarpetaController::class, 'reactivar'])->name('carpetas.restaurar');
    Route::get('/carpetas/obsoletas', [CarpetaController::class, 'obsoletas'])->name('carpetas.obsoletas');
    Route::get('/carpetas/{id}', [CarpetaController::class, 'showObsoleta'])->name('carpetas.obsoletas.show');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/getAppointments', [CalendarController::class, 'getAppointments'])->name('calendar.getAppointments');
    Route::post('/calendar/store', [CalendarController::class, 'store'])->name('calendar.store');
    Route::delete('/calendar/destroy/{id}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::put('/calendar/edit/{id}', [CalendarController::class, 'update'])->name('calendar.edit');

    Route::post('/carpetas/{id}/visibility', [CarpetaController::class, 'updateVisibility'])->name('carpeta.visibility');
    Route::put('/archivos/{id}/permisos', [ArchivoController::class, 'updatePermissions'])->name('archivo.permiso');

});

Route::middleware('isModerator')->group(function () {
   

    Route::post('/admin/mi_unidad', [CarpetaController::class, 'store'])->name('mi_unidad.store');

    Route::put('/file-system/editar', [CarpetaController::class, 'update_folder'])->name('file-system.update_folder');
    Route::post('/file-system/archivar/carpeta/{id}', [CarpetaController::class, 'marcarObsoleta'])->name('file-system.absoleta');



    Route::get('/admin/mi_unidad/carpeta/{id}/edit', [CarpetaController::class, 'edit'])->name('mi_unidad.edit');

    Route::post('/upload-zip', [FiLeSystemController::class, 'uploadZip'])->name('upload.zip');
    // Rutas de administrador
    Route::get('/file-system', [FiLeSystemController::class, 'index'])->name('file-system.home');
    Route::get('/file-system/get-folders', [FiLeSystemController::class, 'getFolder'])->name('file-system.folders');
    Route::get('/file-system/{id}/files', [FileSystemController::class, 'getFiles'])->name('folders.files');
    Route::post('/file-system/create', [FileSystemController::class, 'createFile'])->name('file-system.create_file');
    Route::post('/file-system', [FileSystemController::class, 'compartir'])->name('folders.share');
    Route::put('/file-system/editar', [CarpetaController::class, 'update_folder'])->name('file-system.update_folder');
    Route::delete('/file-system/eliminar/carpeta/{id}', [CarpetaController::class, 'destroy'])->name('file-system.destroy');
    Route::post('/file-system/archivar/carpeta/{id}', [CarpetaController::class, 'marcarObsoleta'])->name('file-system.absoleta');
    Route::get('/file-system/search', [FileSystemController::class, 'search'])->name('file-system.search');
    Route::post('/file-system/create-subfolder', [CarpetaController::class, 'store_subfolder'])->name('file-system.store_subfolder');
    Route::post('/file-system/upload_file', [ArchivoController::class, 'upload_file'])->name('file-system.upload_file');
    Route::post('/file-system/mover-archivo', [FileSystemController::class, 'moverArchivo'])->name('file-system.move');
    Route::post('/file-system/mover-carpeta', [FileSystemController::class, 'moverCarpeta'])->name('file-system.move-carpeta');
    Route::get('/file-system/descargar/{folderId}', [CarpetaController::class, 'descargarContenidoCarpeta'])->name('file-system.download');
    
    Route::patch('/archivos/{id}/restaurar', [ArchivoController::class, 'restaurar'])->name('archivo.restaurar');
    Route::get('/archivos/papelera', [ArchivoController::class, 'verPapelera'])->name('archivo.papelera');
  
    Route::get('/archivo/{id}', [ArchivoController::class, 'show'])->name('archivo.show');
    Route::get('/archivo/{id}/download', [ArchivoController::class, 'download'])->name('archivo.download');
    Route::get('/archivo/{id}/editar', [ArchivoController::class, 'edit'])->name('archivo.edit');
    Route::delete('/archivo/{id}/delete', [ArchivoController::class, 'moverAPapelera'])->name('archivo.delete');
    Route::post('/upload-zip', [FiLeSystemController::class, 'uploadZip'])->name('upload.zip');
   

});

Route::middleware('isUser')->group(function () {
    Route::post('/admin/mi_unidad', [CarpetaController::class, 'store'])->name('mi_unidad.store');

  
    Route::post('/archivo/{id}/rename', [ArchivoController::class, 'renameFile'])->name('archivo.rename');
    Route::get('/archivos/papelera', [ArchivoController::class, 'verPapelera'])->name('archivo.papelera');
    Route::get('/archivo/{id}', [ArchivoController::class, 'show'])->name('archivo.show');
    Route::get('/archivo/{id}/download', [ArchivoController::class, 'download'])->name('archivo.download');
    Route::get('/archivo/{id}/editar', [ArchivoController::class, 'edit'])->name('archivo.edit');
    Route::delete('/archivo/{id}/delete', [ArchivoController::class, 'moverAPapelera'])->name('archivo.delete');
    Route::patch('/archivos/{id}/restaurar', [ArchivoController::class, 'restaurar'])->name('archivo.restaurar');
    
       Route::post('/upload-zip', [FiLeSystemController::class, 'uploadZip'])->name('upload.zip');
    // Rutas de administrador
    Route::get('/file-system', [FiLeSystemController::class, 'index'])->name('file-system.home');
    Route::get('/file-system/get-folders', [FiLeSystemController::class, 'getFolder'])->name('file-system.folders');
    Route::get('/file-system/{id}/files', [FileSystemController::class, 'getFiles'])->name('folders.files');
    Route::post('/file-system/create', [FileSystemController::class, 'createFile'])->name('file-system.create_file');
    Route::post('/file-system', [FileSystemController::class, 'compartir'])->name('folders.share');
    Route::put('/file-system/editar', [CarpetaController::class, 'update_folder'])->name('file-system.update_folder');
    Route::delete('/file-system/eliminar/carpeta/{id}', [CarpetaController::class, 'destroy'])->name('file-system.destroy');
    Route::post('/file-system/archivar/carpeta/{id}', [CarpetaController::class, 'marcarObsoleta'])->name('file-system.absoleta');
    Route::get('/file-system/search', [FileSystemController::class, 'search'])->name('file-system.search');
    Route::post('/file-system/create-subfolder', [CarpetaController::class, 'store_subfolder'])->name('file-system.store_subfolder');
    Route::post('/file-system/upload_file', [ArchivoController::class, 'upload_file'])->name('file-system.upload_file');
    Route::post('/file-system/mover-archivo', [FileSystemController::class, 'moverArchivo'])->name('file-system.move');
    Route::post('/file-system/mover-carpeta', [FileSystemController::class, 'moverCarpeta'])->name('file-system.move-carpeta');
    Route::get('/file-system/descargar/{folderId}', [CarpetaController::class, 'descargarContenidoCarpeta'])->name('file-system.download');

});


