@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2>Historial de Inicio de Sesión de {{ Auth::user()->name }}</h2>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th scope="col">Fecha</th>
                    <th scope="col">IP</th>
                    <th scope="col">Ciudad</th>
                    <th scope="col">Región</th>
                    <th scope="col">País</th>
                    <th scope="col">Organización</th>
                    <th scope="col">Dispositivo</th>
                    <th scope="col">Coordenadas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loginHistory as $history)
                <tr>
                    <td>{{ $history->created_at }}</td>
                    <td>{{ $history->ip_address }}</td>
                    <td>{{ $history->city }}</td>
                    <td>{{ $history->region }}</td>
                    <td>{{ $history->country }}</td>
                    <td>{{ $history->org }}</td>
                    <td>{{ $history->user_agent }}</td>
                    <td>{{ $history->loc }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex justify-content-center mt-3">
    {{ $loginHistory->links('pagination::bootstrap-4') }}
</div>
@endsection
