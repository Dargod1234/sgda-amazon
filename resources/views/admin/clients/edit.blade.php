@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>Editar Cliente</h1>

        <form action="{{ route('admin.clients.update', $client) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
               
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre de la Empresa/Copropiedad</label>
                        <input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror"
                            value="{{ old('business_name', $client->business_name) }}" required>
                        @error('business_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                 <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre del Representante Legal</label>
                        <input type="text" name="legal_representative_name" class="form-control @error('legal_representative_name') is-invalid @enderror"
                            value="{{ old('legal_representative_name') }}" required>
                        @error('legal_representative_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
            </div>

           <div class="row">
               
                 <div class="col-md-6">
                    <div class="form-group">
                        <label>Tel. del Representante Legal</label>
                        <input type="text" name="legal_representative_phone" class="form-control @error('legal_representative_phone') is-invalid @enderror"
                            value="{{ old('legal_representative_phone') }}" required>
                        @error('legal_representative_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>



                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email del Representante Legal</label>
                        <input type="text" name="legal_representative_email" class="form-control @error('legal_representative_email') is-invalid @enderror"
                            value="{{ old('legal_representative_email') }}" required>
                        @error('legal_representative_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email Corporativo</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $client->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Telefono Corporativo</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone', $client->phone) }}" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>NIT/CC</label>
                        <input type="text" name="nit_cc" class="form-control @error('nit_cc') is-invalid @enderror"
                            value="{{ old('nit_cc', $client->nit_cc) }}" required>
                        @error('nit_cc')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Propietario del Contrato</label>
                        <select name="contract_owner" class="form-control @error('contract_owner') is-invalid @enderror" required>
                            <option value="">Seleccionar...</option>
                            <option value="Jennifer" {{ old('contract_owner', $client->contract_owner) == 'Jennifer' ? 'selected' : '' }}>Jennifer</option>
                            <option value="Igamocol" {{ old('contract_owner', $client->contract_owner) == 'Igamocol' ? 'selected' : '' }}>Igamocol</option>
                            <option value="Rodolfo" {{ old('contract_owner', $client->contract_owner) == 'Rodolfo' ? 'selected' : '' }}>Rodolfo</option>
                        </select>
                        @error('contract_owner')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Inicio del Contrato</label>
                        <input type="date" name="contract_start" class="form-control @error('contract_start') is-invalid @enderror"
                            value="{{ old('contract_start', $client->contract_start->format('Y-m-d')) }}" required>
                        @error('contract_start')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Fin del Contrato</label>
                        <input type="date" name="contract_end" class="form-control @error('contract_end') is-invalid @enderror"
                            value="{{ old('contract_end', $client->contract_end->format('Y-m-d')) }}" required>
                        @error('contract_end')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Direccion Corporativa</label>
                <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                    value="{{ old('address', $client->address) }}">
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Notas</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes', $client->notes) }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar Cliente
                </button>
                <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection