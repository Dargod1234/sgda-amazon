@extends('layouts.admin')

@section('content')
<div class="container px-4 py-4">
    <div class="row mb-4">
        <div class="col-sm-6">
            <h1 class="m-0">Historial de Actividades</h1>
        </div>
    </div>

    <!-- Formulario para filtrar por fechas -->
    <form method="GET" action="{{ route('activities.index') }}" class="mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Fecha de inicio:</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate ?? '' }}">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">Fecha de fin:</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate ?? '' }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary m-0">Filtrar</button>
            </div>
        </div>
    </form>

    @if ($activities->isNotEmpty())
        <hr>
        <h5>Actividades Registradas</h5>
        <div class="table-responsive p-4">
            <table class="table table-striped table-bordered custom-table">
                <thead class="thead-dark">
                    <tr>
                        <th>Usuario</th>
                        <th>Acci¨®n</th>
                        <th>Archivo</th>
                        <th>Fecha</th>
                        <th>Actividad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($activities as $activity)
                        <tr>
                            <td>{{ $activity->user->name }}</td>
                            <td>{{ ucfirst($activity->action) }}</td>
                            <td>
                                @if (isset($files[$activity->file_id]))
                                    {{ $files[$activity->file_id]->nombre }}
                                @else
                                    <span class="text-danger">Archivo Eliminado</span>
                                @endif
                            </td>
                            <td>{{ $activity->performed_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if (isset($files[$activity->file_id]))
                                    <a href="{{ route('files.activity', $files[$activity->file_id]->id) }}"
                                        class="btn btn-info btn-sm">
                                        <i class="bi bi-activity"></i>
                                    </a>
                                @else
                                    <button class="btn btn-secondary btn-sm" disabled>
                                        No disponible
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3">
                {{ $activities->appends(request()->except('page'))->links('vendor.pagination.pagination') }}
            </div>
        </div>
    @else
        <p class="text-muted">No hay actividades registradas.</p>
    @endif
</div>
@endsection
