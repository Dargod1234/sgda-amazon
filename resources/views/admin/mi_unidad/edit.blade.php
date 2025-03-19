@extends('layouts.admin')

@section('content')

<br>
<div class="d-flex justify-content-center flex-column">
    <div class="row col-md-12 pt-10 justify-content-center">
        <h1>Editar Carpeta</h1>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Editar Carpeta</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('mi_unidad.update_folder') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $carpeta->id }}">
                        <div class="form-group">
                            <label for="nombre">Nombre de la Carpeta</label>
                            <input type="text" name="nombre" class="form-control" value="{{ $carpeta->nombre }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        <a href="{{ route('mi_unidad.index') }}" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
