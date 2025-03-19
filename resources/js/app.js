import './bootstrap';

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid'; // Para ver las horas
import interactionPlugin from '@fullcalendar/interaction';
import axios from 'axios';

// Create a new store instance.


document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');

    if (calendarEl) {
        var calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
            initialView: 'timeGridWeek',
            slotMinTime: '06:00:00',
            slotMaxTime: '20:00:00',
            selectable: true,
            events: function (fetchInfo, successCallback, failureCallback) {
                axios.get('/calendar/getAppointments')
                    .then(response => {
                        const events = response.data.map(appointment => ({
                            id: appointment.id, // Incluye el ID aquí
                            title: appointment.title,
                            start: appointment.start,
                            end: appointment.end,
                            description: appointment.description, // Asegúrate de incluir cualquier propiedad extra que necesites
                        }));
                        successCallback(events);
                    })
                    .catch(error => {
                        console.error("Error al obtener citas:", error);
                        failureCallback(error);
                    });
            },
            select: function (info) {
                // Mostrar modal para agregar cita
                $('#addAppointmentModal').modal('show');
                document.getElementById('addAppointmentStart').value = info.start.toISOString().slice(0, 16);
                document.getElementById('addAppointmentEnd').value = info.end.toISOString().slice(0, 16);
                document.getElementById('addAppointmentTitle').value = "";
                document.getElementById('addAppointmentDescription').value = "";
            },
            eventClick: function (info) {
                console.log("Evento seleccionado:", info.event); // Agrega esto para ver toda la información del evento
                $('#editAppointmentModal').modal('show');
                document.getElementById('editAppointmentTitle').value = info.event.title;
                document.getElementById('editAppointmentDescription').value = info.event.extendedProps.description;
                document.getElementById('editAppointmentStart').value = info.event.start.toISOString().slice(0, 16);
                document.getElementById('editAppointmentEnd').value = info.event.end.toISOString().slice(0, 16);
                document.getElementById('editAppointmentId').value = info.event.id;
            }
        });

        document.getElementById('addAppointmentForm').onsubmit = function (event) {
            event.preventDefault();
            const title = document.getElementById('addAppointmentTitle').value;
            const description = document.getElementById('addAppointmentDescription').value;
            const start = document.getElementById('addAppointmentStart').value;
            const end = document.getElementById('addAppointmentEnd').value;

            // Agregar un console.log para depurar
            console.log("Guardando cita:", { title, description, start, end });

            axios.post('/calendar/store', {
                title: title,
                description: description,
                start: start,
                end: end,
            }).then(response => {
                calendar.addEvent({
                    id: response.data.id,
                    title: response.data.title,
                    description: response.data.description,
                    start: start,
                    end: end,
                });
                $('#addAppointmentModal').modal('hide');
            }).catch(error => {
                console.error("Error al guardar la cita:", error);
                alert("Hubo un problema al guardar la cita.");
            });
        };



        document.getElementById('editAppointmentForm').onsubmit = function (event) {
            event.preventDefault();
            const id = document.getElementById('editAppointmentId').value; // Obtiene el ID del campo oculto
            const title = document.getElementById('editAppointmentTitle').value;
            const description = document.getElementById('editAppointmentDescription').value;
            const start = document.getElementById('editAppointmentStart').value;
            const end = document.getElementById('editAppointmentEnd').value;

            // Agrega un console.log para depurar
            console.log("Actualizando cita:", { id, title, description, start, end }); // Verifica que el ID no esté vacío

            // Aquí se envía la solicitud PUT
            axios.put(`/calendar/edit/${id}`, {
                title: title,
                description: description,
                start: start,
                end: end,
            }).then(response => {
                // Maneja la respuesta
                const event = calendar.getEventById(id);
                if (event) {
                    event.setProp('title', title);
                    event.setExtendedProp('description', description);  // Asegúrate de incluir la descripción
                    event.setStart(start);
                    event.setEnd(end);
                }
                $('#editAppointmentModal').modal('hide');
            }).catch(error => {
                console.error("Error al actualizar la cita:", error);
                alert("Hubo un problema al actualizar la cita.");
            });
        };


        document.getElementById('deleteAppointmentButton').onclick = function () {
            const id = document.getElementById('editAppointmentId').value;
            console.log(id);
            deleteAppointment(id);
        };


    } else {
        console.error("El elemento del calendario no se encontró.");
    }
    calendar.render();
});

document.addEventListener("DOMContentLoaded", function () {
    const pickr = Pickr.create({
        el: '#color-picker', // Cambia el selector para que coincida con el id
        theme: 'monolith', // Cambia a 'monolith' o 'nano' si prefieres otro tema
        default: '#ebc034', // Color por defecto
        components: {
            preview: true,
            opacity: true,
            hue: true,
            interaction: {
                hex: true,
                rgba: true,
                hsla: true,
                hsva: true,
                input: true,
                clear: true,
                save: true
            }
        }
    });

    // Guardar el color seleccionado en el input oculto
    pickr.on('save', (color) => {
        document.getElementById('color').value = color.toHEXA().toString(); // Cambia el ID del input oculto si es necesario
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const toggles = document.querySelectorAll('.folder-toggle');

    toggles.forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const folderId = this.getAttribute('data-folder-id');
            const folderContent = document.getElementById(`folder-${folderId}`);

            // Alternar la clase 'show'
            if (folderContent.classList.contains('show')) {
                folderContent.classList.remove('show');
            } else {
                folderContent.classList.add('show');
            }
        });
    });
});


document.addEventListener("DOMContentLoaded", function () {
    const folderToggles = document.querySelectorAll(".folder-toggle");

    folderToggles.forEach((toggle) => {
        toggle.addEventListener("click", function (e) {
            e.preventDefault();
            const folderId = this.getAttribute("data-folder-id");
            const folderName = this.innerText.trim(); // Obtiene el nombre de la carpeta

            // Llamada al servidor para obtener datos de la carpeta y sus archivos
            fetch(`/file-system/${folderId}/files`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    const fileTableBody = document.querySelector("#file-table tbody");
                    fileTableBody.innerHTML = ""; // Limpiar la tabla
                    const folderTitle = document.querySelector("#folder-title");
                    folderTitle.innerText = folderName;

                    if (data.archivos.length === 0) {
                        const noFilesRow = document.createElement("tr");
                        noFilesRow.innerHTML = `
                            <td colspan="6" class="text-center">
                                <strong>No hay archivos en la carpeta "${folderName}"</strong>
                            </td>`;
                        fileTableBody.appendChild(noFilesRow);
                        return;
                    }

                    // Procesar cada archivo en la carpeta
                    data.archivos.forEach(file => {
                        const formatDate = (dateString) => {
                            const date = new Date(dateString);
                            return date.toLocaleDateString("es-ES", {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                            });
                        };

                        // Acceso a las propiedades del archivo y permisos
                        const { id: archivoId, nombre: archivoNombre, permisos, created_at, updated_at, edit_url } = file;
                        const puedeVer = permisos[0]?.ver === 1;
                        const puedeDescargar = permisos[0]?.descargar === 1;
                        const puedeEliminar = permisos[0]?.eliminar === 1;
                        const iconoArchivo = obtenerIconoArchivo(archivoNombre);
                        console.log(iconoArchivo);

                        // Crear fila de la tabla para el archivo
                        const row = document.createElement("tr");
                        row.innerHTML = `
                            <td>${iconoArchivo}</td>
                            <td>${archivoNombre}</td>
                            <td>${folderName}</td>
                            <td>${formatDate(created_at)}</td>
                            <td>${formatDate(updated_at)}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    ${puedeVer ? `<a href="/archivo/${archivoId}" class="btn btn-info btn-sm" title="Ver" target="_blank"><i class="bi bi-eye"></i></a>` :
                                `<button class="btn btn-info btn-sm" disabled title="Sin permisos para ver"><i class="bi bi-eye" style="color: gray;"></i></button>`}
                                    ${puedeDescargar ? `<a href="/archivo/${archivoId}/download" class="btn btn-success btn-sm" title="Descargar"><i class="bi bi-download"></i></a>` :
                                `<button class="btn btn-success btn-sm" disabled title="Sin permisos para descargar"><i class="bi bi-download" style="color: gray;"></i></button>`}
                                    ${puedeEliminar ? `<button class="btn btn-danger btn-sm eliminar-btn" data-archivo-id="${archivoId}" title="Eliminar"><i class="bi bi-trash"></i></button>` : ''}
                                    <button class="btn btn-warning btn-sm rename-btn" data-archivo-id="${archivoId}" data-current-name="${archivoNombre}" title="Renombrar"><i class="bi bi-pencil-square"></i></button>
                                    ${edit_url ? `<a href="${edit_url}" class="btn btn-primary btn-sm" title="Editar en Línea" target="_blank"><i class="bi bi-pencil d-flex align-middle w-100 h-100"></i></a>` : ''}
                                </div>
                            </td>
                        `;

                        fileTableBody.appendChild(row);
                    });

                    // Añadir eventos a los nuevos botones de eliminar y renombrar
                    addEventListenersToButtons();
                })
                .catch(error => console.error('Error al obtener archivos:', error));
        });
    });

    // Función para añadir event listeners a los botones de eliminar y renombrar
    function addEventListenersToButtons() {
        // Botones de eliminar
        const deleteButtons = document.querySelectorAll(".eliminar-btn");
        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const archivoId = this.getAttribute('data-archivo-id');
                const folderId = getCurrentFolderId(); // Implementa esta función según tu lógica
                eliminarArchivo(archivoId, folderId);
            });
        });

        // Botones de renombrar
        const renameButtons = document.querySelectorAll(".rename-btn");
        renameButtons.forEach(button => {
            button.addEventListener('click', function () {
                const archivoId = this.getAttribute('data-archivo-id');
                const currentName = this.getAttribute('data-current-name');
                openRenameModal(archivoId, currentName);
            });
        });
    }

    // Función para obtener el ID de la carpeta actual
    function getCurrentFolderId() {
        // Implementa esta función según tu lógica para obtener el folderId actual
        // Por ejemplo, podrías almacenarlo en una variable global cuando se hace clic en una carpeta
        return window.currentFolderId || null;
    }

    // Función para abrir el modal de renombrar
    function openRenameModal(archivoId, currentName) {
        const renameModal = new bootstrap.Modal(document.getElementById('renameModal'));
        document.getElementById('renameArchivoId').value = archivoId;
        document.getElementById('newName').value = currentName;
        document.getElementById('newName').classList.remove('is-invalid');
        renameModal.show();
    }

    // Manejar la sumisión del formulario de renombrado
    document.getElementById('renameForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const archivoId = document.getElementById('renameArchivoId').value;
        const newName = document.getElementById('newName').value.trim();

        if (newName === '') {
            document.getElementById('newName').classList.add('is-invalid');
            return;
        } else {
            document.getElementById('newName').classList.remove('is-invalid');
        }

        // Enviar la solicitud AJAX para renombrar
        fetch(`/archivo/${archivoId}/rename`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ new_name: newName }),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar el nombre en la tabla
                    const renameButton = document.querySelector(`button.rename-btn[data-archivo-id="${archivoId}"]`);
                    if (renameButton) {
                        const row = renameButton.closest('tr');
                        row.querySelector('td:nth-child(2)').textContent = data.new_name;
                        renameButton.setAttribute('data-current-name', data.new_name);
                    }

                    // Cerrar el modal
                    renameModal.hide();

                    // Mostrar mensaje de éxito con SweetAlert2
                    Swal.fire({
                        icon: 'success',
                        title: 'Renombrado Exitoso',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    // Mostrar mensaje de error con SweetAlert2
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                    });
                }
            })
            .catch(error => {
                console.error('Error al renombrar el archivo:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al renombrar el archivo.',
                });
            });
    });

    // Limpiar el formulario cuando el modal se cierra
    renameModal._element.addEventListener('hidden.bs.modal', function () {
        document.getElementById('renameForm').reset();
        document.getElementById('newName').classList.remove('is-invalid');
    });


    //edicion de archivos
    function editarArchivo(archivoId) {
        console.log('Iniciando edición para el archivo:', archivoId);
        return axios.get(`/archivo/${archivoId}/editar`)
            .then(response => {
                console.log(response);
                // Si el servidor devuelve una URL para redirigir
                if (response.data.redirect) {
                    window.location.href = response.data.redirect; // Redirigir a la URL proporcionada por el servidor
                } else {
                    // Manejo de caso donde no hay redirección
                    console.error('No se recibió una URL de redirección válida');
                }
            })
            .catch(error => {
                console.error('Error al obtener los datos para editar:', error);
                if (error.response) {
                    alert(`Error: ${error.response.data.error || 'Se produjo un error al editar el archivo.'}`);
                }
            });
    }
    //eliminacion y cargue de archivos
    function eliminarArchivo(archivoId, folderId) {
        console.log('ID de la carpeta antes de la eliminación:', folderId);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        axios.delete(`/archivo/${archivoId}/delete`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        })
            .then(response => {
                console.log('Archivo eliminado:', response.data);
                if (folderId) {
                    return axios.get(`/file-system/${folderId}/files`);
                } else {
                    console.log('No llegó el folder ID');
                }
            })
            .then(response => {
                const data = response.data;
                const fileTableBody = document.querySelector("#file-table tbody");
                fileTableBody.innerHTML = ""; // Limpiar la tabla

                // Almacenar el nombre de la carpeta
                const folderName = data.nombre;

                if (data.archivos.length === 0) {
                    const noFilesRow = document.createElement("tr");
                    noFilesRow.innerHTML = `
                <td colspan="12" class="text-center">
                    <strong>No hay archivos en esta carpeta.</strong>
                </td>
            `;
                    fileTableBody.appendChild(noFilesRow);
                    return; // Salir de la función si no hay archivos
                }

                // Renderizar cada archivo en la tabla
                data.archivos.forEach(file => {
                    const { id: archivoId, nombre: archivoNombre, permisos, created_at, updated_at } = file;

                    // Permisos
                    const puedeVer = permisos[0]?.ver === 1;
                    const puedeDescargar = permisos[0]?.descargar === 1;
                    const puedeEliminar = permisos[0]?.eliminar === 1;
                    const iconoArchivo = obtenerIconoArchivo(archivoNombre);
                    console.log(iconoArchivo);
                    // Crear fila de la tabla para el archivo
                    const row = document.createElement("tr");
                    row.innerHTML = `
                <td>${iconoArchivo}</td>
                <td>${archivoNombre}</td>
                <td>${folderName}</td>
                <td>${new Date(created_at).toLocaleDateString("es-ES", { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</td>
                <td>${new Date(updated_at).toLocaleDateString("es-ES", { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</td>
                <td>
                    <div class="file-item">
                        ${puedeVer ?
                            `<a href="/archivo/${archivoId}" class="btn btn-info btn-sm" title="Ver"><i class="bi bi-eye"></i></a>` :
                            `<button class="btn btn-info btn-sm" disabled title="Sin permisos para ver"><i class="bi bi-eye" style="color: gray;"></i></button>`}

                        ${puedeDescargar ?
                            `<a href="/archivo/${archivoId}/download" class="btn btn-success btn-sm" title="Descargar"><i class="bi bi-download"></i></a>` :
                            `<button class="btn btn-success btn-sm" disabled title="Sin permisos para descargar"><i class="bi bi-download" style="color: gray;"></i></button>`}
                    </div>
                </td>
            `;



                    // Agregar botón de eliminación
                    if (puedeEliminar) {
                        const deleteButton = document.createElement("span");
                        deleteButton.className = "btn btn-danger btn-sm eliminar-btn";
                        deleteButton.setAttribute("data-archivo-id", archivoId);
                        deleteButton.setAttribute("data-carpeta-id", folderId);
                        deleteButton.innerHTML = `<i class="bi bi-trash"></i>`;
                        deleteButton.addEventListener('click', () => eliminarArchivo(archivoId, folderId));
                        row.querySelector(".file-item").appendChild(deleteButton);
                    }

                    fileTableBody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error al eliminar el archivo:', error);
                if (error.response) {
                    console.error('Detalles del error:', error.response.data);
                }
            });
    }


    //dropzone de las imagenes
    Dropzone.autoDiscover = false;
    // Inicializa Dropzone
    const myDropzone = new Dropzone("#fileDropzone", {
        url: "/admin/mi_unidad/upload", // Cambia esto a la ruta de tu endpoint
        paramName: "file[]", // Utiliza un array para múltiples archivos
        maxFilesize: 10, // Tamaño máximo en MB
        addRemoveLinks: true,
        acceptedFiles: ".jpeg,.jpg,.png,.pdf,.doc,.docx,.xlsx, .pptx",
        autoProcessQueue: true, // Cambiado a false para procesarlo manualmente
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        sending: function (file, xhr, formData) {
            // Agrega el folder_id al formData
            const folderId = document.getElementById("folderSelect").value;
            formData.append("folder_id", folderId); // Añade el folder_id al formData
        }
    });
});
// Botón para iniciar subida
document.getElementById("submitUpload").addEventListener("click", function () {
    const folderId = document.getElementById("folderSelect").value;
    if (!folderId) {
        alert("Selecciona una carpeta antes de subir archivos.");
        return;
    }
    myDropzone.processQueue();
});
document.getElementById('search-form').addEventListener('submit', function (e) {
    e.preventDefault(); // Evitar recargar la página

    const query = document.getElementById('search-input').value;

    // Realizar una solicitud AJAX para obtener los resultados
    fetch(`/file-system/search?query=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('search-results');
            const fileTableBody = document.querySelector("#file-table tbody");
            const folderTitle = document.querySelector("#folder-title");

            resultsDiv.innerHTML = ''; // Limpiar resultados anteriores
            fileTableBody.innerHTML = ''; // Limpiar tabla de archivos
            folderTitle.innerText = "Resultados de búsqueda"; // Título para los resultados

            // Si no se encuentran carpetas ni archivos
            if (data.folders.length === 0 && data.files.length === 0) {
                resultsDiv.innerHTML = '<p class="text-muted">No se encontraron resultados.</p>';
                return;
            }

            let html = '<h5>Resultados de la búsqueda:</h5>';
            html += '<ul class="list-group">';

            // Mostrar carpetas
            if (data.folders.length > 0) {
                html += '<li class="list-group-item active">Carpetas:</li>';
                data.folders.forEach(folder => {
                    html += `
                        <a class="folder-toggle text-decoration-none" 
                           data-folder-id="${folder.id}" 
                           style="margin-left: 15px;" role="button">
                            <i class="bi bi-folder-fill me-2" 
                               style="color: ${folder.color ?? '#ebc034'}; font-size:20px;"></i>
                            ${folder.nombre}
                        </a>
                    `;
                });
            }

            html += '</ul>';
            resultsDiv.innerHTML = html;

            // Mostrar archivos en la tabla directamente
            if (data.files.length > 0) {
                data.files.forEach(file => {
                    const formatDate = (dateString) => {
                        const date = new Date(dateString);
                        return date.toLocaleDateString("es-ES", {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                        });
                    };

                    const { id: archivoId, nombre: archivoNombre, permisos, created_at, updated_at, edit_url, folder_name } = file;
                    const puedeVer = permisos[0]?.ver === 1;
                    const puedeDescargar = permisos[0]?.descargar === 1;
                    const puedeEliminar = permisos[0]?.eliminar === 1;
                    const puedeEditar = permisos[0]?.editar === 1;

                    const icono = obtenerIconoArchivo(archivoNombre);

                    const row = document.createElement("tr");
                    row.innerHTML = `
                        ${puedeVer ? `
                            <td>${icono}</td>
                            <td>${archivoNombre}</td>
                            <td>${folder_name || 'N/A'}</td>
                            <td>${new Date(created_at).toLocaleDateString("es-ES", { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</td>
                            <td>${new Date(updated_at).toLocaleDateString("es-ES", { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    ${puedeVer ? `<a href="/archivo/${archivoId}" class="btn btn-info btn-sm" title="Ver" target="_blank"><i class="bi bi-eye"></i></a>` : ''}
                                    ${puedeDescargar ? `<a href="/archivo/${archivoId}/download" class="btn btn-success btn-sm" title="Descargar"><i class="bi bi-download"></i></a>` : ''}
                                    ${puedeEliminar ? `<button class="btn btn-danger btn-sm eliminar-btn" data-archivo-id="${archivoId}" title="Eliminar"><i class="bi bi-trash"></i></button>` : ''}
                                    ${puedeEditar ? `<a href="${edit_url}" class="btn btn-primary btn-sm" title="Editar en Línea" target="_blank"><i class="bi bi-pencil"></i></a> <button class="btn btn-warning btn-sm rename-btn" data-archivo-id="${archivoId}" data-current-name="${archivoNombre}" title="Renombrar"><i class="bi bi-pencil-square"></i></button>` : ''}
                                </div>
                            </td>
                        ` : ''}
                    `;

                    fileTableBody.appendChild(row);
                });
            }

            // Agregar eventos para carpetas
            document.querySelectorAll('.folder-toggle').forEach(folderElement => {
                folderElement.addEventListener('click', function (e) {
                    e.preventDefault();
                    const folderId = folderElement.getAttribute('data-folder-id');
                    const folderName = folderElement.innerText.trim();

                    // Realizar una solicitud para obtener los archivos de la carpeta seleccionada
                    fetch(`/file-system/${folderId}/files`)
                        .then(response => response.json())
                        .then(data => {
                            fileTableBody.innerHTML = ""; // Limpiar la tabla
                            folderTitle.innerText = folderName;

                            if (data.archivos.length === 0) {
                                const noFilesRow = document.createElement("tr");
                                noFilesRow.innerHTML = `
                                    <td colspan="6" class="text-center">
                                        <strong>No hay archivos en la carpeta "${folderName}"</strong>
                                    </td>`;
                                fileTableBody.appendChild(noFilesRow);
                                return;
                            }

                            data.archivos.forEach(file => {
                                const { id, nombre, permisos, created_at, updated_at, edit_url } = file;
                                const puedeVer = permisos[0]?.ver === 1;
                                const puedeDescargar = permisos[0]?.descargar === 1;
                                const puedeEliminar = permisos[0]?.eliminar === 1;
                                const puedeEditar = permisos[0]?.editar === 1;

                                const icono = obtenerIconoArchivo(nombre);

                                const row = document.createElement("tr");
                                row.innerHTML = `
                                    ${puedeVer ? `
                                        <td>${icono}</td>
                                        <td>${nombre}</td>
                                        <td>${folderName}</td>
                                        <td>${new Date(created_at).toLocaleDateString("es-ES", { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</td>
                                        <td>${new Date(updated_at).toLocaleDateString("es-ES", { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                ${puedeVer ? `<a href="/archivo/${id}" class="btn btn-info btn-sm" title="Ver" target="_blank"><i class="bi bi-eye"></i></a>` : ''}
                                                ${puedeDescargar ? `<a href="/archivo/${id}/download" class="btn btn-success btn-sm" title="Descargar"><i class="bi bi-download"></i></a>` : ''}
                                                ${puedeEliminar ? `<button class="btn btn-danger btn-sm eliminar-btn" data-archivo-id="${id}" title="Eliminar"><i class="bi bi-trash"></i></button>` : ''}
                                                ${puedeEditar ? `<a href="${edit_url}" class="btn btn-primary btn-sm" title="Editar en Línea" target="_blank"><i class="bi bi-pencil"></i></a>` : ''}
                                            </div>
                                        </td>
                                    ` : ''}
                                `;

                                fileTableBody.appendChild(row);
                            });
                        })
                        .catch(error => {
                            console.error('Error al cargar los archivos:', error);
                            alert('Hubo un error al cargar los archivos de la carpeta. Inténtalo de nuevo.');
                        });
                });
            });
        })
        .catch(error => {
            console.error('Error en la búsqueda:', error);
            alert('Hubo un error al realizar la búsqueda. Inténtalo de nuevo.');
        });
});


// Cuando un archivo se sube correctamente, actualiza la lista
myDropzone.on("success", function (file, response) {
    const folderId = document.getElementById("folderSelect").value;
    console.log('Archivo subido correctamente. Actualizando lista en la carpeta ID:', folderId);
    actualizarListaArchivos(folderId);
});

$('#uploadModal').on('hidden.bs.modal', function () {
    myDropzone.removeAllFiles(true);
    document.getElementById("folderSelect").value = "root"; // Reiniciar a la carpeta principal por defecto
});


// Función para actualizar la lista de archivos en la carpeta seleccionada
function actualizarListaArchivos(folderId) {
    // Si folderId es "root", cambia el valor a una cadena vacía para el backend
    const carpetaId = folderId === "root" ? "" : folderId;

    axios.get(`/file-system/${carpetaId}/files`)
        .then(response => {
            const data = response.data;
            const folderName = data.nombre;
            const fileTableBody = document.querySelector("#file-table tbody");
            fileTableBody.innerHTML = ""; // Limpiar tabla actual

            // Manejar caso sin archivos
            if (data.archivos.length === 0) {
                const noFilesRow = document.createElement("tr");
                noFilesRow.innerHTML = `
                        <td colspan="6" class="text-center">
                            <strong>No hay archivos en esta carpeta.</strong>
                        </td>
                    `;
                fileTableBody.appendChild(noFilesRow);
                return;
            }

            console.log(data); // Para depuración

            // Filtrar y renderizar cada archivo en la tabla
            data.archivos.forEach(file => {
                const { id: archivoId, nombre: archivoNombre, created_at, updated_at, permisos } = file;

                // Asumiendo que permisos es un array con al menos un objeto de permisos
                const permiso = permisos.find(p => p.ver !== undefined); // Ajusta según tu estructura
                const puedeVer = permiso ? permiso.ver === true : false;
                const puedeDescargar = permiso ? permiso.descargar === true : false;
                const puedeEliminar = permiso ? permiso.eliminar === true : false;

                // Solo renderizar si puede ver
                if (!puedeVer) {
                    return; // Salta este archivo
                }

                const iconoArchivo = obtenerIconoArchivo(archivoNombre);
                console.log(iconoArchivo);

                // Crear fila de la tabla para el archivo
                const row = document.createElement("tr");
                row.innerHTML = `
                        <td>${iconoArchivo}</td>
                        <td>${archivoNombre}</td>
                        <td>${folderName}</td>
                        <td>${new Date(created_at).toLocaleDateString("es-ES", { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</td>
                        <td>${new Date(updated_at).toLocaleDateString("es-ES", { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</td>
                        <td>
                            <div class="d-flex gap-2">
                                ${puedeVer ?
                        `<a href="/archivo/${archivoId}" class="btn btn-info btn-sm" title="Ver"><i class="bi bi-eye"></i></a>` :
                        `<button class="btn btn-info btn-sm" disabled title="Sin permisos para ver"><i class="bi bi-eye" style="color: gray;"></i></button>`}
    
                                ${puedeDescargar ?
                        `<a href="/archivo/${archivoId}/download" class="btn btn-success btn-sm" title="Descargar"><i class="bi bi-download"></i></a>` :
                        `<button class="btn btn-success btn-sm" disabled title="Sin permisos para descargar"><i class="bi bi-download" style="color: gray;"></i></button>`}
    
                                ${puedeEliminar ?
                        `<button class="btn btn-danger btn-sm eliminar-btn" data-archivo-id="${archivoId}" data-carpeta-id="${folderId}" title="Eliminar"><i class="bi bi-trash"></i></button>` :
                        `<button class="btn btn-danger btn-sm" disabled title="Sin permisos para eliminar"><i class="bi bi-trash" style="color: gray;"></i></button>`}
                            </div>
                        </td>
                    `;

                // Agregar funcionalidad al botón de eliminación si aplica
                if (puedeEliminar) {
                    row.querySelector(".eliminar-btn").addEventListener('click', () => eliminarArchivo(archivoId, folderId));
                }

                fileTableBody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error al actualizar lista de archivos:', error);
        });
}





function obtenerIconoArchivo(nombreArchivo) {
    const extension = nombreArchivo.split('.').pop().toLowerCase();

    switch (extension) {
        case 'pdf':
            return '<svg width="40px" height="40px" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><title>file_type_pdf2</title><path d="M24.1,2.072h0l5.564,5.8V29.928H8.879V30H29.735V7.945L24.1,2.072" style="fill:#909090"/><path d="M24.031,2H8.808V29.928H29.664V7.873L24.03,2" style="fill:#f4f4f4"/><path d="M8.655,3.5H2.265v6.827h20.1V3.5H8.655" style="fill:#7a7b7c"/><path d="M22.472,10.211H2.395V3.379H22.472v6.832" style="fill:#dd2025"/><path d="M9.052,4.534h-.03l-.207,0H7.745v4.8H8.773V7.715L9,7.728a2.042,2.042,0,0,0,.647-.117,1.427,1.427,0,0,0,.493-.291,1.224,1.224,0,0,0,.335-.454,2.13,2.13,0,0,0,.105-.908,2.237,2.237,0,0,0-.114-.644,1.173,1.173,0,0,0-.687-.65A2.149,2.149,0,0,0,9.37,4.56a2.232,2.232,0,0,0-.319-.026M8.862,6.828l-.089,0V5.348h.193a.57.57,0,0,1,.459.181.92.92,0,0,1,.183.558c0,.246,0,.469-.222.626a.942.942,0,0,1-.524.114" style="fill:#464648"/><path d="M12.533,4.521c-.111,0-.219.008-.295.011L12,4.538h-.78v4.8h.918a2.677,2.677,0,0,0,1.028-.175,1.71,1.71,0,0,0,.68-.491,1.939,1.939,0,0,0,.373-.749,3.728,3.728,0,0,0,.114-.949,4.416,4.416,0,0,0-.087-1.127,1.777,1.777,0,0,0-.4-.733,1.63,1.63,0,0,0-.535-.4,2.413,2.413,0,0,0-.549-.178,1.282,1.282,0,0,0-.228-.017m-.182,3.937-.1,0V5.392h.013a1.062,1.062,0,0,1,.6.107,1.2,1.2,0,0,1,.324.4,1.3,1.3,0,0,1,.142.526c.009.22,0,.4,0,.549a2.926,2.926,0,0,1-.033.513,1.756,1.756,0,0,1-.169.5,1.13,1.13,0,0,1-.363.36.673.673,0,0,1-.416.106" style="fill:#464648"/><path d="M17.43,4.538H15v4.8h1.028V7.434h1.3V6.542h-1.3V5.43h1.4V4.538" style="fill:#464648"/><path d="M21.781,20.255s3.188-.578,3.188.511S22.994,21.412,21.781,20.255Zm-2.357.083a7.543,7.543,0,0,0-1.473.489l.4-.9c.4-.9.815-2.127.815-2.127a14.216,14.216,0,0,0,1.658,2.252,13.033,13.033,0,0,0-1.4.288Zm-1.262-6.5c0-.949.307-1.208.546-1.208s.508.115.517.939a10.787,10.787,0,0,1-.517,2.434A4.426,4.426,0,0,1,18.161,13.841ZM13.513,24.354c-.978-.585,2.051-2.386,2.6-2.444C16.11,21.911,14.537,24.966,13.513,24.354ZM25.9,20.895c-.01-.1-.1-1.207-2.07-1.16a14.228,14.228,0,0,0-2.453.173,12.542,12.542,0,0,1-2.012-2.655,11.76,11.76,0,0,0,.623-3.1c-.029-1.2-.316-1.888-1.236-1.878s-1.054.815-.933,2.013a9.309,9.309,0,0,0,.665,2.338s-.425,1.323-.987,2.639-.946,2.006-.946,2.006a9.622,9.622,0,0,0-2.725,1.4c-.824.767-1.159,1.356-.725,1.945.374.508,1.683.623,2.853-.91a22.549,22.549,0,0,0,1.7-2.492s1.784-.489,2.339-.623,1.226-.24,1.226-.24,1.629,1.639,3.2,1.581,1.495-.939,1.485-1.035" style="fill:#dd2025"/><path d="M23.954,2.077V7.95h5.633L23.954,2.077Z" style="fill:#909090"/><path d="M24.031,2V7.873h5.633L24.031,2Z" style="fill:#f4f4f4"/><path d="M8.975,4.457h-.03l-.207,0H7.668v4.8H8.7V7.639l.228.013a2.042,2.042,0,0,0,.647-.117,1.428,1.428,0,0,0,.493-.291A1.224,1.224,0,0,0,10.4,6.79a2.13,2.13,0,0,0,.105-.908,2.237,2.237,0,0,0-.114-.644,1.173,1.173,0,0,0-.687-.65,2.149,2.149,0,0,0-.411-.105,2.232,2.232,0,0,0-.319-.026M8.785,6.751l-.089,0V5.271H8.89a.57.57,0,0,1,.459.181.92.92,0,0,1,.183.558c0,.246,0,.469-.222.626a.942.942,0,0,1-.524.114" style="fill:#fff"/><path d="M12.456,4.444c-.111,0-.219.008-.295.011l-.235.006h-.78v4.8h.918a2.677,2.677,0,0,0,1.028-.175,1.71,1.71,0,0,0,.68-.491,1.939,1.939,0,0,0,.373-.749,3.728,3.728,0,0,0,.114-.949,4.416,4.416,0,0,0-.087-1.127,1.777,1.777,0,0,0-.4-.733,1.63,1.63,0,0,0-.535-.4,2.413,2.413,0,0,0-.549-.178,1.282,1.282,0,0,0-.228-.017m-.182,3.937-.1,0V5.315h.013a1.062,1.062,0,0,1,.6.107,1.2,1.2,0,0,1,.324.4,1.3,1.3,0,0,1,.142.526c.009.22,0,.4,0,.549a2.926,2.926,0,0,1-.033.513,1.756,1.756,0,0,1-.169.5,1.13,1.13,0,0,1-.363.36.673.673,0,0,1-.416.106" style="fill:#fff"/><path d="M17.353,4.461h-2.43v4.8h1.028V7.357h1.3V6.465h-1.3V5.353h1.4V4.461" style="fill:#fff"></svg>';
        case 'doc':
        case 'docx':
            return '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="40px" height="40px" viewBox="0 0 48 48"><path fill="#2d92d4" d="M42.256,6H15.744C14.781,6,14,6.781,14,7.744v7.259h30V7.744C44,6.781,43.219,6,42.256,6z"></path><path fill="#2150a9" d="M14,33.054v7.202C14,41.219,14.781,42,15.743,42h26.513C43.219,42,44,41.219,44,40.256v-7.202H14z"></path><path fill="#2d83d4" d="M14 15.003H44V24.005000000000003H14z"></path><path fill="#2e70c9" d="M14 24.005H44V33.055H14z"></path><path fill="#00488d" d="M22.319,34H5.681C4.753,34,4,33.247,4,32.319V15.681C4,14.753,4.753,14,5.681,14h16.638 C23.247,14,24,14.753,24,15.681v16.638C24,33.247,23.247,34,22.319,34z"></path><path fill="#fff" d="M18.403 19L16.857 26.264 15.144 19 12.957 19 11.19 26.489 9.597 19 7.641 19 9.985 29 12.337 29 14.05 21.311 15.764 29 18.015 29 20.359 19z"></path></svg>'; // Ícono para Word
        case 'xls':
        case 'xlsx':
            return '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="40px" height="40px" viewBox="0 0 48 48"><path fill="#169154" d="M29,6H15.744C14.781,6,14,6.781,14,7.744v7.259h15V6z"></path><path fill="#18482a" d="M14,33.054v7.202C14,41.219,14.781,42,15.743,42H29v-8.946H14z"></path><path fill="#0c8045" d="M14 15.003H29V24.005000000000003H14z"></path><path fill="#17472a" d="M14 24.005H29V33.055H14z"></path><g><path fill="#29c27f" d="M42.256,6H29v9.003h15V7.744C44,6.781,43.219,6,42.256,6z"></path><path fill="#27663f" d="M29,33.054V42h13.257C43.219,42,44,41.219,44,40.257v-7.202H29z"></path><path fill="#19ac65" d="M29 15.003H44V24.005000000000003H29z"></path><path fill="#129652" d="M29 24.005H44V33.055H29z"></path></g><path fill="#0c7238" d="M22.319,34H5.681C4.753,34,4,33.247,4,32.319V15.681C4,14.753,4.753,14,5.681,14h16.638 C23.247,14,24,14.753,24,15.681v16.638C24,33.247,23.247,34,22.319,34z"></path><path fill="#fff" d="M9.807 19L12.193 19 14.129 22.754 16.175 19 18.404 19 15.333 24 18.474 29 16.123 29 14.013 25.07 11.912 29 9.526 29 12.719 23.982z"></path></svg>'; // Ícono para Excel
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="40px" height="40px" viewBox="0 0 30 30" xml:space="preserve"><style type="text/css">	.st0{fill:none;stroke:#6C3DB7;stroke-width:4;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st1{fill:none;stroke:#1F992A;stroke-width:4;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st2{fill:none;stroke:#6A83BA;stroke-width:4;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st3{fill:#8A8AFF;stroke:#8A8AFF;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st4{fill:#6C3DB7;stroke:#6C3DB7;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st5{fill:#A576FF;stroke:#A576FF;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st6{fill:#F2BB41;stroke:#F2BB41;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st7{fill:#E08838;stroke:#E08838;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st8{fill:#1F992A;stroke:#1F992A;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st9{fill:#5EC11E;stroke:#5EC11E;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st10{fill:#E3FAFF;stroke:#E3FAFF;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st11{fill:#FF5093;stroke:#FF5093;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st12{fill:#B7257F;stroke:#B7257F;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st13{fill:#5189E5;stroke:#5189E5;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st14{fill:#6EBAFF;stroke:#6EBAFF;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st15{fill:#EDD977;stroke:#EDD977;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st16{fill:#8C43FF;stroke:#8C43FF;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st17{fill:#5252BA;stroke:#5252BA;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st18{fill:none;stroke:#E3FAFF;stroke-width:4;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}	.st19{fill:#354C75;stroke:#354C75;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}</style><path class="st5" d="M24,26H6c-2.2,0-4-1.8-4-4V8c0-2.2,1.8-4,4-4h18c2.2,0,4,1.8,4,4v14C28,24.2,26.2,26,24,26z"/><path class="st14" d="M24,26H6c-2.2,0-4-1.8-4-4V8c0-2.2,1.8-4,4-4h18c2.2,0,4,1.8,4,4v14C28,24.2,26.2,26,24,26z"/><g>	<path class="st13" d="M6,26h18c2.2,0,4-1.8,4-4v-7l-4-4l-10.4,9.6L9,16l-7,6.4C2.3,24.4,3.9,26,6,26z"/></g><circle class="st10" cx="7" cy="10" r="2"/></svg>'; // Ícono para imágenes
        case 'txt':
            return '<svg width="40px" height="40px" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <defs> <style>.cls-1{fill:#3b64de;}</style> </defs> <title></title> <g id="xxx-word"> <path class="cls-1" d="M325,105H250a5,5,0,0,1-5-5V25a5,5,0,1,1,10,0V95h70a5,5,0,0,1,0,10Z"></path> <path class="cls-1" d="M325,154.83a5,5,0,0,1-5-5V102.07L247.93,30H100A20,20,0,0,0,80,50v98.17a5,5,0,0,1-10,0V50a30,30,0,0,1,30-30H250a5,5,0,0,1,3.54,1.46l75,75A5,5,0,0,1,330,100v49.83A5,5,0,0,1,325,154.83Z"></path> <path class="cls-1" d="M300,380H100a30,30,0,0,1-30-30V275a5,5,0,0,1,10,0v75a20,20,0,0,0,20,20H300a20,20,0,0,0,20-20V275a5,5,0,0,1,10,0v75A30,30,0,0,1,300,380Z"></path> <path class="cls-1" d="M275,280H125a5,5,0,0,1,0-10H275a5,5,0,0,1,0,10Z"></path> <path class="cls-1" d="M200,330H125a5,5,0,0,1,0-10h75a5,5,0,1,1,0,10Z"></path> <path class="cls-1" d="M325,280H75a30,30,0,0,1-30-30V173.17a30,30,0,0,1,30.19-30l250,1.66a30.09,30.09,0,0,1,29.81,30V250A30,30,0,0,1,325,280ZM75,153.17a20,20,0,0,0-20,20V250a20,20,0,0,0,20,20H325a20,20,0,0,0,20-20V174.83a20.06,20.06,0,0,0-19.88-20l-250-1.66Z"></path> <path class="cls-1" d="M163.16,236H152.85V190.92H138.67v-8.24h38.67v8.24H163.16Z"></path> <path class="cls-1" d="M222.23,236H211l-11.8-21-12.5,21h-8.95l16.88-27.77-14.49-25.55h11.17l9.84,17.73,10.43-17.73h9L205.74,207Z"></path> <path class="cls-1" d="M247.15,236H236.84V190.92H222.66v-8.24h38.67v8.24H247.15Z"></path> </g> </g></svg>'; // Ícono para archivos de texto
        case 'zip':
        case 'rar':
            return '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="40px" height="40px" viewBox="0 0 48 48"><linearGradient id="Ja~RXCbVqNAHlfRcVj7wMa_PLvn50bVGAlA_gr1" x1="24" x2="24" y1="18" y2="30" gradientUnits="userSpaceOnUse"><stop offset=".233" stop-color="#41a5ee"></stop><stop offset=".317" stop-color="#3994de"></stop><stop offset=".562" stop-color="#2366b4"></stop><stop offset=".751" stop-color="#154a9b"></stop><stop offset=".86" stop-color="#103f91"></stop></linearGradient><rect width="36" height="12" x="6" y="18" fill="url(#Ja~RXCbVqNAHlfRcVj7wMa_PLvn50bVGAlA_gr1)"></rect><linearGradient id="Ja~RXCbVqNAHlfRcVj7wMb_PLvn50bVGAlA_gr2" x1="24" x2="24" y1="6" y2="18" gradientUnits="userSpaceOnUse"><stop offset=".233" stop-color="#e8457c"></stop><stop offset=".272" stop-color="#e14177"></stop><stop offset=".537" stop-color="#b32c59"></stop><stop offset=".742" stop-color="#971e46"></stop><stop offset=".86" stop-color="#8c193f"></stop></linearGradient><path fill="url(#Ja~RXCbVqNAHlfRcVj7wMb_PLvn50bVGAlA_gr2)" d="M42,18H6V8c0-1.105,0.895-2,2-2h32c1.105,0,2,0.895,2,2V18z"></path><linearGradient id="Ja~RXCbVqNAHlfRcVj7wMc_PLvn50bVGAlA_gr3" x1="24" x2="24" y1="30" y2="42" gradientUnits="userSpaceOnUse"><stop offset=".233" stop-color="#33c481"></stop><stop offset=".325" stop-color="#2eb173"></stop><stop offset=".566" stop-color="#228353"></stop><stop offset=".752" stop-color="#1b673f"></stop><stop offset=".86" stop-color="#185c37"></stop></linearGradient><path fill="url(#Ja~RXCbVqNAHlfRcVj7wMc_PLvn50bVGAlA_gr3)" d="M40,42H8c-1.105,0-2-0.895-2-2V30h36v10C42,41.105,41.105,42,40,42z"></path><rect width="14" height="36" x="17" y="6" opacity=".05"></rect><rect width="13" height="36" x="17.5" y="6" opacity=".07"></rect><linearGradient id="Ja~RXCbVqNAHlfRcVj7wMd_PLvn50bVGAlA_gr4" x1="24" x2="24" y1="6" y2="42" gradientUnits="userSpaceOnUse"><stop offset=".039" stop-color="#f8c819"></stop><stop offset=".282" stop-color="#af4316"></stop></linearGradient><rect width="12" height="36" x="18" y="6" fill="url(#Ja~RXCbVqNAHlfRcVj7wMd_PLvn50bVGAlA_gr4)"></rect><linearGradient id="Ja~RXCbVqNAHlfRcVj7wMe_PLvn50bVGAlA_gr5" x1="24" x2="24" y1="12" y2="42" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#eaad29"></stop><stop offset=".245" stop-color="#d98e24"></stop><stop offset=".632" stop-color="#c0631c"></stop><stop offset=".828" stop-color="#b75219"></stop><stop offset=".871" stop-color="#a94917"></stop><stop offset=".949" stop-color="#943b13"></stop><stop offset="1" stop-color="#8c3612"></stop></linearGradient><path fill="url(#Ja~RXCbVqNAHlfRcVj7wMe_PLvn50bVGAlA_gr5)" d="M24,12c-3.314,0-6,2.686-6,6v24h12V18C30,14.686,27.314,12,24,12z"></path><path d="M20,32c-0.73,0-1.41-0.2-2-0.55v1.14c0.61,0.26,1.29,0.41,2,0.41h8c0.71,0,1.39-0.15,2-0.41v-1.14 C29.41,31.8,28.73,32,28,32H20z M29,22v6c0,0.55-0.45,1-1,1h-2v-2c0-1.1-0.9-2-2-2s-2,0.9-2,2v2h-2c-0.55,0-1-0.45-1-1v-6 c0-0.55-0.45-1-1-1v7c0,1.1,0.9,2,2,2h3v-3c0-0.55,0.45-1,1-1s1,0.45,1,1v3h3c1.1,0,2-0.9,2-2v-7C29.45,21,29,21.45,29,22z" opacity=".05"></path><path d="M29.5,22v6c0,0.83-0.67,1.5-1.5,1.5h-2.5V27c0-0.83-0.67-1.5-1.5-1.5s-1.5,0.67-1.5,1.5v2.5H20 c-0.83,0-1.5-0.67-1.5-1.5v-6c0-0.28-0.22-0.5-0.5-0.5V28c0,1.1,0.9,2,2,2h3v-3c0-0.55,0.45-1,1-1s1,0.45,1,1v3h3c1.1,0,2-0.9,2-2 v-6.5C29.72,21.5,29.5,21.72,29.5,22z M20,32c-0.73,0-1.41-0.2-2-0.55v0.58c0.6,0.3,1.28,0.47,2,0.47h8c0.72,0,1.4-0.17,2-0.47 v-0.58C29.41,31.8,28.73,32,28,32H20z" opacity=".07"></path><linearGradient id="Ja~RXCbVqNAHlfRcVj7wMf_PLvn50bVGAlA_gr6" x1="24" x2="24" y1="21" y2="32" gradientUnits="userSpaceOnUse"><stop offset=".613" stop-color="#e6e6e6"></stop><stop offset=".785" stop-color="#e4e4e4"></stop><stop offset=".857" stop-color="#ddd"></stop><stop offset=".91" stop-color="#d1d1d1"></stop><stop offset=".953" stop-color="#bfbfbf"></stop><stop offset=".967" stop-color="#b8b8b8"></stop></linearGradient><path fill="url(#Ja~RXCbVqNAHlfRcVj7wMf_PLvn50bVGAlA_gr6)" d="M32,23v5c0,2.2-1.8,4-4,4h-8c-2.2,0-4-1.8-4-4v-5c0-1.105,0.895-2,2-2h0v7 c0,1.105,0.895,2,2,2h3v-3c0-0.552,0.448-1,1-1h0c0.552,0,1,0.448,1,1v3h3c1.105,0,2-0.895,2-2v-7C31.1,21,32,21.9,32,23z"></path></svg>'; // Ícono para archivos comprimidos
        case 'mp4':
        case 'mov':
        case 'avi':
            return '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="40px" height="40px"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M13 3H7C5.89543 3 5 3.89543 5 5V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V9H15.5C14.1193 9 13 7.88071 13 6.5V3ZM18.8178 8C18.7184 7.78333 18.5801 7.5841 18.4074 7.41308L14.5366 3.57905C14.3784 3.42235 14.1968 3.2947 14 3.19953V6.5C14 7.32843 14.6716 8 15.5 8H18.8178Z" fill="#1d78cd" fill-opacity="0.24"></path> <path d="M10 16.1169V11.8831C10 11.4944 10.424 11.2544 10.7572 11.4543L14.2854 13.5713C14.6091 13.7655 14.6091 14.2345 14.2854 14.4287L10.7572 16.5457C10.424 16.7456 10 16.5056 10 16.1169Z" fill="#1c7bc4"></path> </g></svg>'; // Ícono para vídeos
        case 'mp3':
            return '<svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" width="40px" height="40px" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <defs> <style>.cls-1{fill:#7e2e7f;}</style> </defs> <title></title> <g id="xxx-word"> <path class="cls-1" d="M325,105H250a5,5,0,0,1-5-5V25a5,5,0,0,1,10,0V95h70a5,5,0,0,1,0,10Z"></path> <path class="cls-1" d="M325,154.83a5,5,0,0,1-5-5V102.07L247.93,30H100A20,20,0,0,0,80,50v98.17a5,5,0,0,1-10,0V50a30,30,0,0,1,30-30H250a5,5,0,0,1,3.54,1.46l75,75A5,5,0,0,1,330,100v49.83A5,5,0,0,1,325,154.83Z"></path> <path class="cls-1" d="M300,380H100a30,30,0,0,1-30-30V275a5,5,0,0,1,10,0v75a20,20,0,0,0,20,20H300a20,20,0,0,0,20-20V275a5,5,0,0,1,10,0v75A30,30,0,0,1,300,380Z"></path> <path class="cls-1" d="M275,280H125a5,5,0,0,1,0-10H275a5,5,0,0,1,0,10Z"></path> <path class="cls-1" d="M200,330H125a5,5,0,0,1,0-10h75a5,5,0,0,1,0,10Z"></path> <path class="cls-1" d="M325,280H75a30,30,0,0,1-30-30V173.17a30,30,0,0,1,30-30h.2l250,1.66a30.09,30.09,0,0,1,29.81,30V250A30,30,0,0,1,325,280ZM75,153.17a20,20,0,0,0-20,20V250a20,20,0,0,0,20,20H325a20,20,0,0,0,20-20V174.83a20.06,20.06,0,0,0-19.88-20l-250-1.66Z"></path> <path class="cls-1" d="M178.71,236h-9.8V189.32L154.14,236h-5l-14.84-46.68V236h-7.85V182.68h15L152.62,217l11-34.34h15.12Z"></path> <path class="cls-1" d="M200.08,236h-9.61V182.68H212.3q9.34,0,13.85,4.71a16.37,16.37,0,0,1-.37,22.95,17.49,17.49,0,0,1-12.38,4.53H200.08Zm0-29.37h11.37q4.45,0,6.8-2.19a7.58,7.58,0,0,0,2.34-5.82,8,8,0,0,0-2.17-5.62q-2.17-2.34-7.83-2.34H200.08Z"></path> <path class="cls-1" d="M249.37,212.45v-7.58h4.88a12.88,12.88,0,0,0,7.3-2,6.53,6.53,0,0,0,2.93-5.82,6.76,6.76,0,0,0-2.48-5.7,10.16,10.16,0,0,0-6.39-1.91q-7.89,0-10.74,7.73l-8.79-1.52a16.93,16.93,0,0,1,6.86-10,21.57,21.57,0,0,1,12.95-3.87,21.26,21.26,0,0,1,12.87,3.89,12.24,12.24,0,0,1,5.33,10.41,12.49,12.49,0,0,1-2.87,8.28,9.9,9.9,0,0,1-7.09,3.75,13.59,13.59,0,0,1,8.42,4.34,12.38,12.38,0,0,1,3.18,8.55,13.87,13.87,0,0,1-5.53,11.31q-5.53,4.43-14.71,4.43-8.48,0-14-4.32a18.47,18.47,0,0,1-7-9.9l9.1-2.23q2.73,8.91,11.8,8.91a11.39,11.39,0,0,0,7.56-2.4,7.32,7.32,0,0,0,2.87-5.8,8.47,8.47,0,0,0-2.48-6q-2.48-2.6-7.48-2.6Z"></path> </g> </g></svg>'
        case 'wav':
            return '<svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" width="40px" height="40px" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <defs> <style>.cls-1{fill:#a85f2e;}</style> </defs> <title></title> <g id="xxx-word"> <path class="cls-1" d="M325,105H250a5,5,0,0,1-5-5V25a5,5,0,1,1,10,0V95h70a5,5,0,0,1,0,10Z"></path> <path class="cls-1" d="M325,154.83a5,5,0,0,1-5-5V102.07L247.93,30H100A20,20,0,0,0,80,50v98.17a5,5,0,0,1-10,0V50a30,30,0,0,1,30-30H250a5,5,0,0,1,3.54,1.46l75,75A5,5,0,0,1,330,100v49.83A5,5,0,0,1,325,154.83Z"></path> <path class="cls-1" d="M300,380H100a30,30,0,0,1-30-30V275a5,5,0,0,1,10,0v75a20,20,0,0,0,20,20H300a20,20,0,0,0,20-20V275a5,5,0,0,1,10,0v75A30,30,0,0,1,300,380Z"></path> <path class="cls-1" d="M275,280H125a5,5,0,0,1,0-10H275a5,5,0,0,1,0,10Z"></path> <path class="cls-1" d="M200,330H125a5,5,0,0,1,0-10h75a5,5,0,1,1,0,10Z"></path> <path class="cls-1" d="M325,280H75a30,30,0,0,1-30-30V173.17a30,30,0,0,1,30-30h.2l250,1.66a30.09,30.09,0,0,1,29.81,30V250A30,30,0,0,1,325,280ZM75,153.17a20,20,0,0,0-20,20V250a20,20,0,0,0,20,20H325a20,20,0,0,0,20-20V174.83a20.06,20.06,0,0,0-19.88-20l-250-1.66Z"></path> <path class="cls-1" d="M191.62,182.68,177.36,236H167.29l-10.62-39.22L147,236h-9.88l-14.57-53.32h10.2l10.31,38.87,9.61-38.87h9.73L173,221.55l10.12-38.87Z"></path> <path class="cls-1" d="M237.52,236H227.25L223,223.3H202.48L198.3,236h-8.2l17.7-53.32h11.84Zm-17.11-20.39-7.77-23.79-7.77,23.79Z"></path> <path class="cls-1" d="M261.39,236h-9.77l-16.45-53.32h10.16l12.23,40.86,12.5-40.86h8Z"></path> </g> </g></svg>'; // Ícono para archivos de audio
        case 'bmp':
            return '<svg height="40px" width="40px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path style="fill:#E2E5E7;" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"></path> <path style="fill:#B0B7BD;" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"></path> <polygon style="fill:#CAD1D8;" points="480,224 384,128 480,128 "></polygon> <path style="fill:#84BD5A;" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16 V416z"></path> <g> <path style="fill:#FFFFFF;" d="M99.968,384c-4.608,0-7.808-3.456-7.808-7.936v-72.656c0-4.608,3.2-7.936,7.808-7.936h35.952 c16.768,0,25.84,11.392,25.84,24.432c0,5.744-1.664,11.392-7.024,16.128c10.096,3.968,14.576,11.76,14.576,21.232 c-0.016,14.704-10,26.736-29.184,26.736H99.968z M135.904,311.072h-26.992v19.056h26.992c5.504,0,8.96-3.456,8.96-10.24 C144.864,315.68,141.408,311.072,135.904,311.072z M108.912,368.384h31.216c14.848,0,14.848-22.64,0-22.64 c-9.712,0-21.104,0-31.216,0V368.384z"></path> <path style="fill:#FFFFFF;" d="M201.456,327.84v47.328c0,5.648-4.608,8.832-9.2,8.832c-4.096,0-7.68-3.184-7.68-8.832v-72.016 c0-6.656,5.648-8.848,7.68-8.848c3.696,0,5.872,2.192,8.048,4.624l28.16,37.984l29.152-39.408c4.24-5.232,14.592-3.2,14.592,5.648 v72.016c0,5.648-3.584,8.832-7.664,8.832c-4.608,0-8.192-3.184-8.192-8.832V327.84l-21.248,26.864 c-4.592,5.648-10.352,5.648-14.576,0L201.456,327.84z"></path> <path style="fill:#FFFFFF;" d="M290.176,303.152c0-4.224,3.328-8.848,8.704-8.848h29.552c16.64,0,31.616,11.136,31.616,32.496 c0,20.224-14.976,31.472-31.616,31.472h-21.36v16.896c0,5.648-3.584,8.832-8.192,8.832c-4.224,0-8.704-3.184-8.704-8.832 L290.176,303.152L290.176,303.152z M307.056,310.432v31.856h21.36c8.576,0,15.36-7.552,15.36-15.488 c0-8.96-6.784-16.368-15.36-16.368L307.056,310.432L307.056,310.432z"></path> </g> <path style="fill:#CAD1D8;" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"></path> </g></svg>'
        case 'pptx':
            return '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="40px" height="40px" viewBox="0 0 48 48"><path fill="#FF8A65" d="M41,10H25v28h16c0.553,0,1-0.447,1-1V11C42,10.447,41.553,10,41,10z"></path><path fill="#FBE9E7" d="M24 29H38V31H24zM24 33H38V35H24zM30 15c-3.313 0-6 2.687-6 6s2.687 6 6 6 6-2.687 6-6h-6V15z"></path><path fill="#FBE9E7" d="M32,13v6h6C38,15.687,35.313,13,32,13z"></path><path fill="#E64A19" d="M27 42L6 38 6 10 27 6z"></path><path fill="#FFF" d="M16.828,17H12v14h3v-4.823h1.552c1.655,0,2.976-0.436,3.965-1.304c0.988-0.869,1.484-2.007,1.482-3.412C22,18.487,20.275,17,16.828,17z M16.294,23.785H15v-4.364h1.294c1.641,0,2.461,0.72,2.461,2.158C18.755,23.051,17.935,23.785,16.294,23.785z"></path></svg>';
        default:
            return '<svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" height="40px" width="40px" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <defs> <style>.cls-1{fill:#514848;}</style> </defs> <title></title> <g id="xxx-word"> <path class="cls-1" d="M325,105H250a5,5,0,0,1-5-5V25a5,5,0,1,1,10,0V95h70a5,5,0,1,1,0,10Z"></path> <path class="cls-1" d="M325,154.83a5,5,0,0,1-5-5V102.07L247.93,30H100A20,20,0,0,0,80,50v98.17a5,5,0,0,1-10,0V50a30,30,0,0,1,30-30H250a5,5,0,0,1,3.54,1.46l75,75A5,5,0,0,1,330,100v49.83A5,5,0,0,1,325,154.83Z"></path> <path class="cls-1" d="M300,380H100a30,30,0,0,1-30-30V275a5,5,0,0,1,10,0v75a20,20,0,0,0,20,20H300a20,20,0,0,0,20-20V275a5,5,0,0,1,10,0v75A30,30,0,0,1,300,380Z"></path> <path class="cls-1" d="M275,280H125a5,5,0,0,1,0-10H275a5,5,0,0,1,0,10Z"></path> <path class="cls-1" d="M200,330H125a5,5,0,0,1,0-10h75a5,5,0,0,1,0,10Z"></path> <path class="cls-1" d="M325,280H75a30,30,0,0,1-30-30V173.17a30,30,0,0,1,30-30h.2l250,1.66a30.09,30.09,0,0,1,29.81,30V250A30,30,0,0,1,325,280ZM75,153.17a20,20,0,0,0-20,20V250a20,20,0,0,0,20,20H325a20,20,0,0,0,20-20V174.83a20.06,20.06,0,0,0-19.88-20l-250-1.66Z"></path> <path class="cls-1" d="M203.38,220.69h-7.62v-5.27a7.14,7.14,0,0,1,1.07-4.18,25,25,0,0,1,4.71-4.34q5.55-4.26,5.55-9a7.59,7.59,0,0,0-2.17-5.7,7.75,7.75,0,0,0-5.64-2.11q-8,0-9.49,11.13l-8.52-1.52q.78-8.32,6.15-13.07a18.78,18.78,0,0,1,12.87-4.75,17.67,17.67,0,0,1,12.34,4.43,14.3,14.3,0,0,1,4.88,11,14.82,14.82,0,0,1-1.35,6.23,14.48,14.48,0,0,1-3.07,4.57,92,92,0,0,1-7.27,5.68,5.52,5.52,0,0,0-2,2.21A16,16,0,0,0,203.38,220.69Zm1.56,5.55V236h-9.18v-9.77Z"></path> </g> </g></svg>'; // Ícono genérico para otros tipos de archivo
    }
}



function deleteAppointment(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás recuperar esta cita!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminarla!'
    }).then((result) => {
        if (result.isConfirmed) {
            axios.delete(`/calendar/destroy/${id}`)
                .then(response => {
                    if (response.data.success) {
                        // Recargar los eventos después de eliminar
                        Swal.fire(
                            '¡Eliminado!',
                            'La cita ha sido eliminada con éxito.',
                            'success'
                        );

                        location.reload(true);

                    } else {
                        Swal.fire(
                            'Error',
                            response.data.message, // Muestra el mensaje de error
                            'error'
                        );
                    }
                })
                .catch(error => {
                    console.error("Error al eliminar la cita:", error);
                    Swal.fire(
                        'Error',
                        'Hubo un problema al eliminar la cita.',
                        'error'
                    );
                });
        }
    });
}

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
})
