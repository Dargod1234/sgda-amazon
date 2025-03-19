@extends('layouts.app')

@section('content')
<style>
   .cui-statusbar .cui-toolbar-buttondock {
    height: 22px;
    margin-left: 5px;
    margin-right: 5px;
    margin-top: 0;
    padding: 0;
    display:none;
    }   
</style>
<div class="container my-5">
    <h2 class="mb-4 text-center">Visualizacion de Archivo</h2>
    
    @if(isset($embedUrl))
        <div class="ratio ratio-16x9 mb-4 position-relative">
            <!-- Overlay transparente para bloquear clics -->
            <div 
                class="position-absolute top-0 start-0 h-100" 
                style="z-index: 10; background: transparent; width:75% !important;" 
                id="iframeOverlay">
            </div>
            <iframe id="iframeOverlay" src="{{ $embedUrl }}" title="Archivo Embebido" style=".cui-statusbar .cui-toolbar-buttondock {
    height: 22px;
    margin-left: 5px;
    margin-right: 5px;
    margin-top: 0;
    padding: 0;
    display:none;
    }   "></iframe>
        </div>

    @else
        <div class="alert alert-danger text-center" role="alert">
            <strong>Error:</strong> No se pudo cargar el archivo para su visualizacion.
        </div>
    @endif
    <script>
        // Bloquear clic izquierdo y derecho en toda la p¨¢gina
        document.addEventListener('mousedown', function(event) {
         
            event.preventDefault(); // Previene cualquier acci¨®n del mouse
        });

        // Opcional: Permitir clics dentro del iframe si se remueve el overlay
        document.getElementById('iframeOverlay').addEventListener('click', function() {
            alert('Los clics dentro del iframe estan deshabilitados.');
        });
        
        
    </script>
     <script>
        // Ocultar todos los elementos <span>
        document.querySelectorAll('span').forEach(function(span) {
            span.style.display = 'none';
        });
    </script>
</div>
@endsection
