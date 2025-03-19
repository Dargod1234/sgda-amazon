<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Archivo;
use App\Models\ActivityLog;


class ActivityLogController extends Controller
{
    /**
     * Muestra el historial de actividades de un archivo específico.
     *
     * @param int $fileId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function showFileActivity($fileId)
    {
        // Obtener el archivo especificado, incluyendo los eliminados
        $file = Archivo::withTrashed()->findOrFail($fileId);

        // Obtener el historial de actividades del archivo
        $activities = ActivityLog::where('file_id', $fileId)
            ->with('user')  // Cargar el usuario asociado a cada actividad
            ->orderBy('performed_at', 'desc')  // Ordenar por fecha de la acción
            ->get();

        // Retornar la vista con el historial de actividades
        return view('admin.activity_logs.file_activity', compact('file', 'activities'));
    }

    public function index(Request $request)
    {
        // Obtener los parámetros de filtrado de fechas del formulario
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
    
        // Crear la consulta base para las actividades
        $query = ActivityLog::with('user')->orderBy('performed_at', 'desc');
    
        // Aplicar filtro de fechas si están presentes
        if ($startDate) {
            $query->whereDate('performed_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('performed_at', '<=', $endDate);
        }
    
        // Obtener las actividades paginadas
        $activities = $query->paginate(10);
    
        // Obtener los archivos relacionados
        $fileIds = $activities->pluck('file_id')->unique();
        $files = Archivo::withTrashed()->whereIn('id', $fileIds)->get()->keyBy('id');
    
        // Retornar la vista con las actividades filtradas
        return view('admin.activity_logs.index', compact('activities', 'files', 'startDate', 'endDate'));
    }

}
