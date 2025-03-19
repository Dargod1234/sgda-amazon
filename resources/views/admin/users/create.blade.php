@extends('layouts.admin')
@section('content')
    <br>
    <div class="d-flex justify-content-center flex-column">
        <div class="row col-md-12 pt-10 justify-content-center">
            <h1>Registro de usuarios</h1>
        </div>
        <div class="row justify-content-left">
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Completa los datos</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ url('/admin/users') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="name">Nombre del usuario</label>
                                        <input type="text" value="{{ old('name') }}" name="name"
                                            class="form-control" required>
                                        @error('name')
                                            <small style="color: red">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="email">Correo</label>
                                        <input type="email" value="{{ old('email') }}" name="email"
                                            class="form-control" required>
                                        @error('email')
                                            <small style="color: red">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="password">Contraseña</label>
                                        <input type="password" name="password" class="form-control" required>
                                        @error('password')
                                            <small style="color: red">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="password_confirmation">Repetir Contraseña</label>
                                        <input type="password" name="password_confirmation" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            {{-- Solo mostrar el selector de roles si el usuario actual es un administrador --}}
                            @if (auth()->user()->role === 'admin')
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="role">Rol</label>
                                            <select name="role" class="form-control">
                                                <option value="admin">Admin</option>
                                                <option value="moderator">Moderator</option>
                                                <option value="user">User</option>
                                            </select>
                                            @error('role')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="{{ url('admin/users') }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-floppy"></i> Guardar
                                        Usuario</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
