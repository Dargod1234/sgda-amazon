@extends('layouts.admin')

@section('content')
<div class="container mt-5">
    <h1>Clientes</h1>

    <div class="mb-3">
        <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Cliente
        </a>
        <a href="{{ route('admin.clients.import.form') }}" class="btn btn-success">
            <i class="fas fa-file-import"></i> Cargue Masivo
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('import_errors'))
        <div class="alert alert-danger alert-dismissible fade show">
            <h5>Errores en la importación:</h5>
            <ul class="mb-0">
                @foreach (session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Contenedor con doble scroll -->
    <div class="table-responsive overflow-auto" style="max-height: 70vh; border: 1px solid #dee2e6;">
        <table class="table table-hover align-middle" style="min-width: 1500px;">
            <thead class="table-light sticky-top bg-light">
                <tr>
                    <th class="text-nowrap">Empresa/Copropiedad</th>
                    <th class="text-nowrap">R.L. Nombre</th>
                    <th class="text-nowrap">R.L. Teléfono</th>
                    <th class="text-nowrap">R.L. Email</th>
                    <th class="text-nowrap">Email</th>
                    <th class="text-nowrap">Teléfono</th>
                    <th class="text-nowrap">NIT/CC</th>
                    <th class="text-nowrap">Propietario</th>
                    <th class="text-nowrap">Inicio Contrato</th>
                    <th class="text-nowrap">Fin Contrato</th>
                    <th class="text-nowrap">Dirección</th>
                    <th class="text-nowrap">Notas</th>
                    <th class="text-nowrap sticky-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($clients as $client)
                <tr>
                    <td>{{ $client->business_name }}</td>
                    <td>{{ $client->legal_representative_name }}</td>
                    <td>{{ $client->legal_representative_phone }}</td>
                    <td>{{ $client->legal_representative_email }}</td>
                    <td>{{ $client->email }}</td>
                    <td>{{ $client->phone }}</td>
                    <td>{{ $client->nit_cc }}</td>
                    <td>{{ $client->contract_owner }}</td>
                    <td>{{ $client->contract_start->format('d/m/Y') }}</td>
                    <td>
                        {{ $client->contract_start->format('d/m/Y') }} -
                        {{ $client->contract_end->format('d/m/Y') }}
                                    <!-- Alertas de vencimiento -->
                            @if ($client->isAboutToExpire())
                            <span class="badge badge-danger ml-2">
                                VENCE EN {{ $client->daysUntilExpiration() }} DÍAS
                            </span>
                            @elseif($client->isNearExpiration())
                            <span class="badge badge-warning ml-2">
                                VENCE EN {{ $client->daysUntilExpiration() }} DÍAS
                            </span>
                        @endif
                    </td>
                    <td>{{ Str::limit($client->address, 20) }}</td>
                    <td>{{ Str::limit($client->notes, 30) }}</td>
                    <td>
                        <div class="d-flex gap-2 flex-nowrap">
                            <a href="{{ route('admin.clients.assets.index', $client) }}" 
                               class="btn btn-sm btn-info"
                               data-bs-toggle="tooltip" 
                               title="Activos">
                                <i class="fas fa-cube"></i>
                            </a>
                            <a href="{{ route('admin.clients.edit', $client) }}" 
                               class="btn btn-sm btn-warning"
                               data-bs-toggle="tooltip" 
                               title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.clients.destroy', $client) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-sm btn-danger"
                                        data-bs-toggle="tooltip" 
                                        title="Eliminar"
                                        onclick="return confirm('¿Eliminar cliente permanentemente?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $clients->links() }}
    </div>
</div>
@endsection