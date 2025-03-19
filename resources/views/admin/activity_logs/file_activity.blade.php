@extends('layouts.admin')

@section('content')
    <div class="container px-4 py-4">
        <h3 class="mb-4">Historial de actividades para el archivo: {{ $file->nombre ?? 'Archivo Eliminado' }}</h3>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td>{{ $activity->user->name }}</td>
                            <td>{{ ucfirst($activity->action) }}</td>
                            <td>{{ $activity->performed_at }}</td> <!-- Formateo de la fecha -->
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">No hay actividades registradas para este archivo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <a href="{{ route('activities.index') }}" class="btn btn-primary btn-activity">Ver todas las actividades</a>
        </div>
    </div>
@endsection
