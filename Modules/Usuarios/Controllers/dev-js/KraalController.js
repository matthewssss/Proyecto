jQuery(function () {
    loadMonitores();
});


function loadMonitores() {
    $.ajax({
        url: '../Controllers/Servlet.php',
        type: 'GET',
        data: { action: 'getMonitoresFiltrados' },
        dataType: 'json',
        success: function (response) {

            if (response.sesion) {
                window.location.href = '/403';
            }

            if (!response.error) {
                $('#monitoresTableBody').html(response.html);
                loadTable();
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin hijos inscritos',
                    text: response.message || 'No se pudo cargar la información de los monitores.',
                    confirmButtonText: 'Entendido',
                    customClass: { icon: 'mi-popup-central' },
                }).then(() => {
                    window.location.href = '/asociados'; // Cambia '/ruta-especifica' por la URL deseada
                });
            }
        },
        error: function (error) {
            window.location.href = "/500";
        }
    });
}

function loadTable() {
    var table = $('#tablaMonitores').DataTable({
        responsive: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            zeroRecords: "No se encontraron resultados.",
            emptyTable: "No hay usuarios disponibles."
        },
        paging: false,
        lengthChange: false,
        searching: true,
        ordering: true,
        info: false,
        autoWidth: false,
        order: [[0, 'asc']],


        // Función para filtrar según las selecciones
        initComplete: function () {
            // Filtros por columnas
            let api = this.api();

            $('#filter-rol').on('change', function () {
                api.column(3).search(this.value).draw();  // Filtro por rol (columna 4)
            });
            $('#filter-rama').on('change', function () {
                api.column(4).search(this.value).draw();  // Filtro por rama (columna 5)
            });
        }
    });
}