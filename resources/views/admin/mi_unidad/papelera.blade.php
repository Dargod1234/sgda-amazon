@extends('layouts.admin')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">Papelera</h1>

    <!-- Mostrar mensajes de éxito -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Mostrar Carpetas Eliminadas -->
    <div class="mb-5">
        <h2>Carpetas Eliminadas</h2>
        <table class="table table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Movido a Papelera Por</th>
                    <th>Fecha de Eliminación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($carpetas as $carpeta)
                    <tr>
                        <td>{{ $carpeta->nombre }}</td>
                        <td>
                            @if($carpeta->eliminadoPor)
                                {{ $carpeta->eliminadoPor->name }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $carpeta->deleted_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="d-flex">
                                <!-- Restaurar Carpeta -->
                                <form action="{{ route('carpeta.restaurar', $carpeta->id) }}" method="POST" class="me-2">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                    </button>
                                </form>

                                <!-- Eliminar Permanentemente Carpeta -->
                                <form action="{{ route('carpeta.eliminar-permanentemente', $carpeta->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> Eliminar Permanentemente
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Enlaces de paginación para carpetas -->
        <div class="d-flex justify-content-center">
            {{ $carpetas->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Mostrar Archivos Eliminados -->
    <div class="mb-5">
    <h2>Archivos Eliminados</h2>
    <table class="table table-hover table-striped">
        <thead class="table-dark">
            <tr>
                <th>Icon</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Movido a Papelera Por</th>
                <th>Fecha de Eliminación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($archivos as $archivo)
                <tr>
                   <td>
                    <!-- Determinar ícono según tipo de archivo -->
                    @php
                        $extension = strtolower(pathinfo($archivo->nombre, PATHINFO_EXTENSION));
                    @endphp
                    @if($extension === 'pdf')
                        <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 18px;"></i>
                    @elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                        <i class="bi bi-file-earmark-image text-success" style="font-size: 18px;"></i>
                    @elseif(in_array($extension, ['doc', 'docx']))
                        <i class="bi bi-file-earmark-word text-primary" style="font-size: 18px;"></i>
                    @elseif(in_array($extension, ['xls', 'xlsx']))
                        <i class="bi bi-file-earmark-spreadsheet text-success" style="font-size: 18px;"></i>
                    @elseif(in_array($extension, ['ppt', 'pptx']))
                        <i class="bi bi-file-earmark-slides text-warning" style="font-size: 18px;"></i>
                    @else
                        <i class="bi bi-file-earmark" style="font-size: 18px;"></i>
                    @endif
                </td>
                    <td>
                        {{ $archivo->nombre }}
                    </td>
                    <td>{{ strtoupper($extension) }}</td>
                    <td>{{ $archivo->deleted_by ? $archivo->eliminadoPor->name : 'N/A' }}</td>
                    <td>{{ $archivo->deleted_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <div class="d-flex">
                            <!-- Restaurar Archivo -->
                            <form action="{{ route('archivo.restaurar', $archivo->id) }}" method="POST" class="me-2">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                </button>
                            </form>

                            <!-- Eliminar Permanentemente Archivo -->
                            <form action="{{ route('archivo.eliminar-permanentemente', $archivo->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i> Eliminar Permanentemente
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Enlaces de paginación para archivos -->
    <div class="d-flex justify-content-center">
        {{ $archivos->links('pagination::bootstrap-5') }}
    </div>
</div>
</div>
@endsection
