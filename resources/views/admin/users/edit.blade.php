@extends('layouts.admin')

@section('content')
    <br>
    <div class="d-flex justify-content-center flex-column">
        <div class="row col-md-12 pt-10 justify-content-center">
            <h1>Editar usuario</h1>
        </div>
        <div class="row justify-content-left">
            <div class="col-md-6">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">Completa los datos</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ url('/admin/users', $user->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Nombre -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="name">Nombre del usuario</label>
                                        <input type="text" value="{{ old('name', $user->name) }}" name="name"
                                            class="form-control" required>
                                        @error('name')
                                            <small style="color: red">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Correo -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="email">Correo</label>
                                        <input type="email" value="{{ old('email', $user->email) }}" name="email"
                                            class="form-control" required>
                                        @error('email')
                                            <small style="color: red">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Nueva contraseña -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="new_password">Nueva contraseña</label>
                                        <div class="input-group">
                                            <input type="password" id="new_password" name="password" class="form-control"
                                                placeholder="Ingrese nueva contraseña (opcional)">
                                            <button type="button" class="btn btn-outline-secondary mt-0" id="togglePassword">
                                                <i class="bi bi-eye-slash"></i>
                                            </button>
                                        </div>
                                        <small>Deje este espacio en blanco si no desea cambiar la contraseña.</small>
                                        @error('password')
                                            <small style="color: red">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Confirmar contraseña -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="password_confirmation">Confirmar nueva contraseña</label>
                                        <input type="password" name="password_confirmation" class="form-control"
                                            placeholder="Confirme la nueva contraseña (opcional)">
                                    </div>
                                </div>
                            </div>

                            <!-- Selección de rol (solo visible si el usuario es admin) -->
                            @if (auth()->user()->role === 'admin')
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="role">Rol</label>
                                            <select name="role" class="form-control">
                                                <option value="admin" {{ (old('role', $user->role) === 'admin') ? 'selected' : '' }}>Admin</option>
                                                <option value="moderator" {{ (old('role', $user->role) === 'moderator') ? 'selected' : '' }}>Moderator</option>
                                                <option value="user" {{ (old('role', $user->role) === 'user') ? 'selected' : '' }}>User</option>
                                            </select>
                                            @error('role')
                                                <small style="color: red">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Botones -->
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="{{ url('admin/users') }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-success"><i class="bi bi-floppy"></i> Actualizar
                                        usuario</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const togglePasswordButton = document.getElementById('togglePassword');
        const passwordField = document.getElementById('new_password');

        togglePasswordButton.addEventListener('click', function() {
            const currentType = passwordField.getAttribute('type');
            if (currentType === 'password') {
                passwordField.setAttribute('type', 'text');
                this.innerHTML = '<i class="bi bi-eye"></i>';
            } else {
                passwordField.setAttribute('type', 'password');
                this.innerHTML = '<i class="bi bi-eye-slash"></i>';
            }
        });
    </script>
@endsection
