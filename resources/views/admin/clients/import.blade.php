@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>Importar Clientes desde Excel</h1>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session('import_errors'))
            <div class="alert alert-warning alert-dismissible fade show">
                <h5>Errores detectados:</h5>
                <ul class="mb-0">
                    @foreach (session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <a href="{{ asset('formatos/plantilla_clientes.xlsx') }}" class="btn btn-success mb-3" download>
                    <i class="fas fa-download"></i> Descargar Plantilla
                </a>

                <form action="{{ route('admin.clients.import') }}" method="POST" enctype="multipart/form-data"
                    id="import-form">
                    @csrf

                    <div class="form-group">
                        <label>Archivo Excel</label>
                        <div class="custom-file">
                            <input type="file" name="file"
                                class="custom-file-input @error('file') is-invalid @enderror" id="customFile" required
                                accept=".xlsx,.xls,.csv">
                            <label class="custom-file-label" for="customFile">Seleccionar archivo</label>
                        </div>
                        @error('file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <i class="fas fa-upload"></i> Importar
                    </button>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary">
                        Cancelar
                    </a>
                </form>
            </div>
        </div>
    </div>

@section('scripts')
    <script>
        // Mostrar nombre de archivo
        document.getElementById('customFile').addEventListener('change', function(e) {
            var fileName = document.getElementById("customFile").files[0].name;
            var nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
        });

        // Deshabilitar doble click
        document.getElementById('import-form').addEventListener('submit', function() {
            document.getElementById('submit-btn').setAttribute('disabled', 'true');
        });
    </script>
@endsection
@endsection
