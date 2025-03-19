@extends('layouts.admin')

@section('content')
    <div id="calendar" style="all:unset"></div>

   <!-- Modal para agregar citas (Actualizado) -->
    <div class="modal fade" id="addAppointmentModal" tabindex="-1" aria-labelledby="addAppointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addAppointmentModalLabel">Agregar Cita</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <form id="addAppointmentForm" class="needs-validation">
                         <div class="mb-3">
                            <label for="addAppointmentTitle" class="form-label">Título</label>
                            <input type="text" class="form-control" id="addAppointmentTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="addAppointmentDescription" class="form-label">Descripción</label>
                            <textarea class="form-control" id="addAppointmentDescription" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="addAppointmentStart" class="form-label">Fecha y Hora de Inicio</label>
                            <input type="datetime-local" class="form-control" id="addAppointmentStart" required>
                        </div>
                        <div class="mb-3">
                            <label for="addAppointmentEnd" class="form-label">Fecha y Hora de Fin</label>
                            <input type="datetime-local" class="form-control" id="addAppointmentEnd" required>
                        </div>
                        <div class="mb-3">
                            <label for="addClient" class="form-label">Cliente</label>
                            <select class="form-select" id="addClient" required>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->business_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addUser" class="form-label">Trabajador</label>
                            <select class="form-select" id="addUser" required>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                             <button type="submit" class="btn btn-primary w-100">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para editar citas (Actualizado) -->
    <div class="modal fade" id="editAppointmentModal" tabindex="-1" aria-labelledby="editAppointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="editAppointmentModalLabel">Editar Cita</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAppointmentForm">
                        <input type="hidden" id="editAppointmentId">
                        <div class="mb-3">
                            <label for="editAppointmentTitle" class="form-label">Título</label>
                            <input type="text" class="form-control" id="editAppointmentTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="editAppointmentDescription" class="form-label">Descripción</label>
                            <textarea class="form-control" id="editAppointmentDescription" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editAppointmentStart" class="form-label">Fecha y Hora de Inicio</label>
                            <input type="datetime-local" class="form-control" id="editAppointmentStart" required>
                        </div>
                        <div class="mb-3">
                            <label for="editAppointmentEnd" class="form-label">Fecha y Hora de Fin</label>
                            <input type="datetime-local" class="form-control" id="editAppointmentEnd" required>
                        </div>
                  
                        <div class="mb-3">
                            <label for="editClient" class="form-label">Cliente</label>
                            <select class="form-select" id="editClient" required>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->business_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editUser" class="form-label">Trabajador</label>
                            <select class="form-select" id="editUser" required>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                              <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-warning w-50 me-1">Actualizar</button>
                            <button type="button" id="deleteAppointmentButton"
                                class="btn btn-danger w-50 ms-1">Eliminar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para eliminar citas -->
    <div class="modal fade" id="deleteAppointmentModal" tabindex="-1" aria-labelledby="deleteAppointmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteAppointmentModalLabel">Eliminar Cita</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar esta cita?</p>
                    <input type="hidden" id="deleteAppointmentId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmDeleteAppointment" class="btn btn-danger">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
@endsection
