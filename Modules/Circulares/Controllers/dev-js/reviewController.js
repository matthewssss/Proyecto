/*
Botones
Ver - ✔
Editar - 
Borrar - ✔
Clonar - 
Descargar - ✔
Enviar - ✔
Crear - ✔
*/

jQuery(function () {
    cargarPermisos();
});

function cargarPermisos() {
    $.ajax({
        url: '../Controllers/Servlet.php',
        method: 'POST',
        data: { action: 'getSession' },
        dataType: 'json',
        success: function (response) {
            if (response.exits) {
                // Si la sesión es válida, cargar los datos
                cargarDatos(true);
            } else {
                window.location.href = "/403";    
            }
        },
        error: function (xhr, status, error) {
            window.location.href = "/500";
        }
    });
}


function cargarDatos(reloadDataTable) {
    $.ajax({
        url: '../Controllers/Servlet.php',
        method: 'POST',
        data: { action: 'getAllData' },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                $('#tablaNoEnviados tbody').html(response.no_enviados);
                $('#tablaEnviados tbody').html(response.enviados);
                $('#tablaEliminados tbody').html(response.eliminados);

                if (reloadDataTable) {
                    inicializarTabla('#tablaNoEnviados');
                    inicializarTabla('#tablaEnviados');
                    inicializarTabla('#tablaEliminados');
                    loadEvents();
                }
            } else {
                console.error('Error al cargar los datos:', response.message);
            }
        },
        error: function (xhr, status, error) {
            window.location.href = "/500";
        }
    });
}

function inicializarTabla(idTabla) {
    $(idTabla).DataTable({
        responsive: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            zeroRecords: "No se encontraron resultados.",
            emptyTable: "No hay datos disponibles."
        },
        paging: false,
        lengthChange: false,
        searching: true,
        ordering: true,
        info: false,
        autoWidth: false,
        order: [[0, 'asc']]
    });
}

function loadEvents() {
    $(document).on('click', '.action-menu', function () {
        var row = $(this).closest('tr');
        var menu = row.find('.action-dropdown');

        // Toggle visibility of action dropdown
        menu.toggle();

        // Cerrar menú si se hace clic fuera de él
        $(document).on('click', function (event) {
            if (!$(event.target).closest('.action-menu').length) {
                menu.hide();
            }
        });
    });

    $(document).on('click', '.btn-ver', function () {
        var row = $(this).closest('tr');
        var titulo = row.data('titulo');
        var archivoPath = row.data('path');

        // Abrir el modal con SweetAlert
        Swal.fire({
            title: titulo,
            html: ` 
                <iframe src="${archivoPath}" width="100%" height="500px" frameborder="0"></iframe>
                <div class="mt-3 text-center">
                    <button id="download-btn" class="btn btn-primary btnPrincipal">Descargar Circular</button>
                    <button id="close-btn" class="btn btn-secondary btnPrincipal">Cerrar</button>
                </div>
            `,
            width: '80%',
            showCloseButton: true,
            showConfirmButton: false,
            willOpen: () => {
                // Una vez el modal se ha abierto, agregamos los eventos de los botones
                $('#download-btn').on('click', function () {
                    var a = document.createElement('a');
                    a.href = archivoPath;
                    a.download = '';  // Forzar la descarga
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                });

                // Acción de cerrar
                $('#close-btn').on('click', function () {
                    Swal.close();  // Cerrar el modal
                });
            }
        });
    });    

    $(document).on('click', '.btn-borrar', borrarModal);

    $(document).on('click', '.btn-enviar', enviarCircular);

    $(document).on('click', '.btn-descargar', function () {
        var row = $(this).closest('tr');
        var archivoPath = row.data('path');

        // Crear un enlace de descarga
        var a = document.createElement('a');
        a.href = archivoPath;  // Establecer la URL del archivo
        a.download = '';  // Esto fuerza la descarga del archivo
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);  // Eliminar el enlace temporal después de hacer clic
    });

    $(document).on('click', '.btn-editar', function () {
        const datos = obtenerDatosDesdeFila(this);
        openCircularModal('editar', datos);
    });
    
    $(document).on('click', '.btn-clonar', function () {
        const datos = obtenerDatosDesdeFila(this);
        openCircularModal('clonar', datos);
    });

    $("#btn-crear-circular").on("click", openCircularModal);

    function obtenerDatosDesdeFila(boton) {
        const $tr = $(boton).closest('tr');
        return {
            id: $tr.data('id'),
            titulo: $tr.data('titulo'),
            path: $tr.data('path'),
            pernoctas: $tr.data('pernoctas'),
            fechaInicio: $tr.data('fecha-inicio'),
            fechaFin: $tr.data('fecha-fin'),
            rama: $tr.data('rama'),
            ubicacion: $tr.data('ubicacion'),
        };
    }
    
}


function borrarModal() {
    var row = $(this).closest('tr');
    var id = row.data('id');
    var titulo = row.data('titulo');

    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡Tu circular '" + titulo + "' se borrará, puedes ir a la pestaña de eliminados y revertirlo en cualquier momento.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, borrar',
        cancelButtonText: 'Cancelar',
        customClass: {
            icon: 'mi-popup-central'
        },
    }).then((result) => {
        if (result.isConfirmed) {
            // Llamada AJAX para borrar
            $.ajax({
                url: '../Controllers/Servlet.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'deleteFirstTime',  // Acción que se procesa en el servlet
                    id: id  // Pasamos el id de la fila
                },
                success: function (response) {
                    // Maneja la respuesta aquí
                    if (response.status === 'success') {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: 'La circular ha sido eliminada correctamente.',
                            icon: 'success',
                            confirmButtonText: 'Aceptar',
                            customClass: {
                                icon: 'mi-popup-central'
                            },
                        }).then(() => {
                            cargarDatos(false);
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'Hubo un problema al eliminar la circular.',
                            icon: 'error',
                            confirmButtonText: 'Aceptar',
                            customClass: {
                                icon: 'mi-popup-central'
                            },
                        });
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un error en la conexión.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        customClass: {
                            icon: 'mi-popup-central'
                        },
                    });
                }
            });
        }
    });
}

function enviarCircular() {
    var row = $(this).closest('tr');
    var id = row.data('id');

    Swal.fire({
        title: '¿Estás seguro de enviar esta circular?',
        text: "¿Confirmas el envío de esta circular? Los usuarios serán notificados y no podrás volver a editarla.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar',
        customClass: {
            icon: 'mi-popup-central'
        },
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Enviando circular...',
                text: 'Por favor espera mientras procesamos la solicitud.',
                allowOutsideClick: false,  // No se puede cerrar si se hace clic fuera
                showConfirmButton: false,  // Sin botón de confirmación
                customClass: {
                    icon: 'mi-popup-central'
                },
                onOpen: () => {
                    Swal.showLoading();  // Mostrar el loader
                }
            });

            $.ajax({
                url: '../Controllers/Servlet.php',
                method: 'POST',
                data: { action: 'enviarCircular', id: id },
                success: function (response) {
                    console.log(response);
                    Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: 'success',
                        title: 'La circular ha sido enviada correctamente.',
                        showConfirmButton: false,
                        timer: 3500,
                        timerProgressBar: true,
                        customClass: {
                          popup: 'swal2-toast-custom'
                        },
                    }).then(() => {
                        cargarDatos(false);
                    });
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo enviar la circular.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        customClass: {
                            icon: 'mi-popup-central'
                        },
                    });
                }
        });
}
    });
}