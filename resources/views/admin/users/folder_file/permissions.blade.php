@extends('layouts.admin')
@section('content')
    <div class="container py-5">
        <form action="{{ route('guardar.permisos', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 100%;">Gestor de Archivos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($folders as $folder)
                        <tr>
                            <td>
                                <div class="list-group">
                                    @include('partials.folder', ['folder' => $folder])
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="submit" class="btn btn-success">Guardar Permisos</button>
        </form>
    </div>
@endsection
@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Alternar visibilidad de carpetas
        const folderToggles = document.querySelectorAll(".folder-toggle");

        folderToggles.forEach((toggle) => {
            toggle.addEventListener("click", function(e) {
                e.preventDefault(); // Evita el comportamiento predeterminado del enlace
                const folderId = this.getAttribute("data-folder-id");
                const collapseElement = document.getElementById(`folder-${folderId}`);

                if (collapseElement) {
                    collapseElement.classList.toggle("show");
                }
            });
        });

       
    });
</script>
@endpush
