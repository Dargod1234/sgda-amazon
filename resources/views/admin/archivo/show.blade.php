@extends('layouts.app')
@section('content')
    <div class="container mt-4">
        <h2>Visualizando: {{ $archivo->nombre }}</h2>
        @if (isset($embedUrl))
            <script type="text/javascript">
                // Redirigir a la URL de edición en OneDrive en una nueva ventana
                window.open("{{ $embedUrl }}", "_blank");


                setTimeout(function() {
                    window.location.href = "http://localhost:8000/file-system"; // Redirige a la ruta deseada
                }, 2000);
            </script>
        @else
            <p>No se puede visualizar este archivo.</p>
        @endif
    </div>
@endsection
