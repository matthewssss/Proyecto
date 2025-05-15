const eventsData = loadEvents();
let iso;

function loadEvents() {
    $.ajax({
        url: '../Controllers/Servlet.php',
        method: 'GET',
        data: { action: 'getEvents' },
        dataType: 'json',
        success: function (data) {
            loadData();
            return data;  // Llamamos al callback con los datos cargados
        },
        error: function (xhr, status, error) {
            window.location.href = "/500";
        }
    });
}

jQuery(function () {
    document.getElementById('searchAsociado').addEventListener('input', function () {
        let timeout;
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            applyFilters();
        }, 300);
    });
    document.getElementById('resetSearchAsociado').addEventListener('click', () => {
        document.getElementById('searchAsociado').value = '';
        applyFilters();
    });

    loadEventsMobile();
});

function loadData() {
    $.ajax({
        url: '../Controllers/Servlet.php',
        method: 'GET',
        data: { action: 'loadFiles' },
        dataType: 'json',
        success: function (response) {
            $('#cards-ramas').html(response.recientes);
            $('#circulares').html(response.todas);
            isotope();
            $('#filtroMes').select2();
            fullCalendar();
            document.getElementById('filtroTodos').checked = true;
            document.querySelectorAll('.filtro-rama').forEach(cb => cb.checked = true);

            const isMobile = window.innerWidth < 992;
            if (!isMobile) applyFilters();

            setTimeout(() => {
                iso.isotope({ 'data-fecha': true });
            }, 100);

            $('.shareBtnClass').on('click', function () {
                const filePath = this.getAttribute('data-path');  // Obtiene el path del archivo
                if (navigator.share) {
                    // Si el navegador soporta el API de compartir
                    navigator.share({
                        title: document.title,
                        url: filePath   // Comparte la URL del archivo en lugar de la del index
                    }).then(() => {
                        console.log('Contenido compartido con éxito');
                    }).catch((error) => {
                        console.log('Error al compartir', error);
                    });
                } else {
                    // Si el navegador no soporta el API de compartir, mostrar un enlace para copiar manualmente
                    alert('Tu navegador no soporta la función de compartir, pero puedes copiar el enlace: ' + filePath);
                }
            });

            if (response.idRol) {//TODO AQUI FILTROSSS
                $("#buttonCreate").html('<button class="btn btn-primary btnPrincipal" id="createCircular">Crear circular</button>');
                $("#createCircular").on("click", openCircularModal);
            }
        },
        error: function (xhr, status, error) {
            //TODO ERROR CIRCULARES
            window.location.href = "/500";
        }
    });
}

function isotope() {
    iso = $('#circulares').isotope({
        itemSelector: '.circular',
        layoutMode: 'fitRows',
        getSortData: {
            'data-fecha': '[data-fecha]', 
            'data-pernoctas': '[data-pernoctas] parseInt', 
            'data-rama': '[data-rama]', 
            'data-titulo': '[data-titulo]',
            'custom-rama': function (itemElem) {
                // Orden personalizado de ramas
                const ordenRamas = { 'Lobatos': 1, 'Exploradores': 2, 'Pioneros': 3, 'General': 4 };
                const rama = itemElem.getAttribute('data-rama') || 'General';
                return ordenRamas[rama] || 99; // Si no se encuentra, lo ponemos al final
            }
        }
    });

    // Para Select2
    $('#filtroMes').on('change.select2', applyFilters);

    // Para checkboxes individuales
    document.querySelectorAll('.filtro-rama').forEach(cb => {
        cb.addEventListener('change', () => {
            document.getElementById('filtroTodos').checked = Array.from(document.querySelectorAll('.filtro-rama')).every(cb => cb.checked);
            applyFilters();
        });
    });

    // Para "todos"
    document.getElementById('filtroTodos').addEventListener('change', e => {
        const checked = e.target.checked;
        document.querySelectorAll('.filtro-rama').forEach(cb => cb.checked = checked);
        applyFilters();
    });


    // Ordenar
    document.getElementById('ordenarPor').addEventListener('change', function () {
        sortBy(this.value);
    });
}

function applyFilters() {
    const isMobile = window.innerWidth < 992;

    // Comprobamos si hay campos
    const modalMes = document.getElementById('filtroMesModal');
    const escritorioMes = document.getElementById('filtroMes');

    // Si no existe ninguno, no filtramos por mes
    let selectedMonth = null;
    if (modalMes) {
        const value = modalMes.value;
        selectedMonth = value ? parseInt(value, 10) : null;
    } else if (escritorioMes) {
        const value = escritorioMes.value;
        selectedMonth = value ? parseInt(value, 10) : null;
    }

    let ramasExcluidas = [];
    let todosChecked = true;
    let ramaCheckboxes = [];

    if (isMobile && document.querySelectorAll('.filtro-rama-modal').length) {
        todosChecked = document.getElementById('filtroTodosModal').checked;
        ramaCheckboxes = document.querySelectorAll('.filtro-rama-modal');
    } else if (!isMobile && document.querySelectorAll('.filtro-rama').length) {
        todosChecked = document.getElementById('filtroTodos').checked;
        ramaCheckboxes = document.querySelectorAll('.filtro-rama');
    }

    if (!todosChecked && ramaCheckboxes.length) {
        ramasExcluidas = Array.from(ramaCheckboxes)
            .filter(cb => !cb.checked)
            .map(cb => cb.value);
    }

    // Recolectar texto de búsqueda
    const searchInput = isMobile
        ? document.getElementById('searchAsociadoModal')
        : document.getElementById('searchAsociado');

    const searchText = searchInput ? searchInput.value.trim().toLowerCase() : "";

    // Aplicamos filtros
    iso.isotope({
        filter: function () {
            const $item = $(this);
            const rama = $item.data('rama') ? $item.data('rama').toLowerCase() : "";
            const mes = $item.data('mes');
            const lugar = $item.data('lugar') ? $item.data('lugar').toLowerCase() : "";
            const pernoctas = $item.data('pernoctas') ? String($item.data('pernoctas')).toLowerCase() : "";
            const titulo = $item.data('titulo') ? $item.data('titulo').toLowerCase() : "";

            const matchRama = todosChecked || !ramasExcluidas.includes($item.data('rama'));
            const matchMes = selectedMonth === null || parseInt(mes) === selectedMonth;
            const matchSearch = searchText === "" ||
                rama.includes(searchText) ||
                lugar.includes(searchText) ||
                pernoctas.includes(searchText) ||
                titulo.includes(searchText);

            return matchRama && matchMes && matchSearch;
        }
    });

    // Mostrar/ocultar mensaje de "sin resultados"
    let timeout;
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        const visibleItems = document.querySelectorAll('#circulares .circular:not([style*="display: none"])');
        const sinResultados = document.getElementById('sinResultados');
        sinResultados.style.display = visibleItems.length === 0 ? 'block' : 'none';
    }, 500);
}

function fullCalendar() {
    // Primero cargamos los eventos desde el servidor
    $.ajax({
        url: '../Controllers/Servlet.php',
        method: 'GET',
        data: { action: 'getEvents' },
        dataType: 'json',
        success: function (eventsData) {
            const calendarEl = document.getElementById('calendario');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listWeek'
                },
                views: {
                    listWeek: {
                        type: 'list',
                        duration: { days: 30 }, // 30 días en vez de 7
                        buttonText: 'Lista'
                    }
                },
                initialDate: new Date().toISOString().split('T')[0],
                editable: true,
                locale: 'es',
                selectable: true,
                events: eventsData, // Pasa los eventos cargados desde el servidor
            });
            calendar.render();
        },
        error: function (xhr, status, error) {
            const calendarEl = document.getElementById('calendario');
            calendarEl.text('No se pudo cargar el calendario');
        }
    });
}

function sortBy(element) {
    const order = element;
    let sortBy = 'data-fecha';
    let sortAscending = true;

    if (order === "fechaAsc") {
        sortBy = 'data-fecha';
        sortAscending = true;
    } else if (order === "fechaDesc") {
        sortBy = 'data-fecha';
        sortAscending = false;
    } else if (order === "pernoctas") {
        sortBy = 'data-pernoctas';
        sortAscending = false; // Queremos de mayor a menor
    } else if (order === "rama") {
        sortBy = 'custom-rama'; // Definiremos una función custom
        sortAscending = true;
    } else if (order === "titulo") {
        sortBy = 'data-titulo';
        sortAscending = false; // A-Z
    }

    iso.isotope({ sortBy, sortAscending });
}

function loadEventsMobile () {
    document.getElementById('ordenarPorMobile').addEventListener('change', function () {
        sortBy(this.value);
    });

    document.getElementById('searchAsociadoModal').addEventListener('input', function () {
        let timeout;
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            applyFilters();
        }, 300);
    });
    document.getElementById('resetSearchAsociadoModal').addEventListener('click', () => {
        document.getElementById('searchAsociadoModal').value = '';
        applyFilters();
    });

    document.getElementById('btnFiltros').addEventListener('click', loadFilterModal);
}


// Función para mostrar el modal de filtros en móvil
function loadFilterModal() {
    Swal.fire({
        title: 'Filtros',
        html: `
            <div>
                <h5>Filtrar por mes</h5>
                <select id="filtroMesModal">
                    <option value="">Todos</option>
                    <option value="1">Enero</option>
                    <option value="2">Febrero</option>
                    <option value="3">Marzo</option>
                    <option value="4">Abril</option>
                    <option value="5">Mayo</option>
                    <option value="6">Junio</option>
                    <option value="7">Julio</option>
                    <option value="8">Agosto</option>
                    <option value="9">Septiembre</option>
                    <option value="10">Octubre</option>
                    <option value="11">Noviembre</option>
                    <option value="12">Diciembre</option>
                </select>
            </div>
            <div>
                <h5>Filtrar por rama</h5>
                <input type="checkbox" id="filtroTodosModal" checked> Todas las unidades<br>
                <input type="checkbox" class="filtro-rama-modal" value="Lobatos" checked> Lobatos<br>
                <input type="checkbox" class="filtro-rama-modal" value="Exploradores" checked> Exploradores<br>
                <input type="checkbox" class="filtro-rama-modal" value="Pioneros" checked> Pioneros<br>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Aplicar filtros',
        cancelButtonText: 'Cancelar',
        didOpen: () => {
            // Al abrir el modal: control dinámico de los checkboxes
            document.getElementById('filtroTodosModal').addEventListener('change', function () {
                const isChecked = this.checked;
                document.querySelectorAll('.filtro-rama-modal').forEach(cb => cb.checked = isChecked);
            });

            document.querySelectorAll('.filtro-rama-modal').forEach(cb => {
                cb.addEventListener('change', function () {
                    const allChecked = Array.from(document.querySelectorAll('.filtro-rama-modal')).every(cb => cb.checked);
                    document.getElementById('filtroTodosModal').checked = allChecked;
                });
            });
        },
        preConfirm: () => {
            applyModalFilters();
        }
    });
}


// Función para aplicar los filtros desde el modal
function applyModalFilters() {
    // Obtener el mes seleccionado
    const selectedMonthValue = document.getElementById('filtroMesModal').value;
    const selectedMonth = selectedMonthValue ? parseInt(selectedMonthValue, 10) : null;

    // Verificar el estado de los checkboxes del modal
    const filtroTodosModal = document.getElementById('filtroTodosModal').checked;
    const ramasExcluidas = [];

    if (!filtroTodosModal) {
        // Si "Todas las unidades" no está seleccionado, solo las ramas seleccionadas serán consideradas
        document.querySelectorAll('.filtro-rama-modal').forEach(cb => {
            if (!cb.checked) {
                ramasExcluidas.push(cb.value);
            }
        });
    }

    // Obtener el texto de búsqueda
    const searchText = document.getElementById('searchAsociadoModal').value.trim().toLowerCase();

    // Ahora aplicamos el filtro a los elementos visibles en el contenedor
    iso.isotope({
        filter: function () {
            const $item = $(this);
            const rama = $item.data('rama') ? $item.data('rama').toLowerCase() : "";
            const mes = $item.data('mes');
            const lugar = $item.data('lugar') ? $item.data('lugar').toLowerCase() : "";
            const pernoctas = $item.data('pernoctas') ? String($item.data('pernoctas')).toLowerCase() : "";
            const titulo = $item.data('titulo') ? $item.data('titulo').toLowerCase() : "";

            // Filtrar por rama
            const matchRama = filtroTodosModal || !ramasExcluidas.includes($item.data('rama'));
            const matchMes = selectedMonth === null || parseInt(mes) === selectedMonth;

            // Filtrar por búsqueda
            const matchSearch = searchText === "" ||
                rama.includes(searchText) ||
                lugar.includes(searchText) ||
                pernoctas.includes(searchText) ||
                titulo.includes(searchText);

            return matchRama && matchMes && matchSearch;
        }
    });

    // Actualizar el estado de los checkboxes originales
    document.querySelectorAll('.filtro-rama').forEach(cb => {
        const modalCheckbox = document.querySelector(`.filtro-rama-modal[value="${cb.value}"]`);
        if (modalCheckbox) {
            cb.checked = modalCheckbox.checked;
        }
    });

    // Actualizar el checkbox de "Todas las unidades" si es necesario
    const allChecked = Array.from(document.querySelectorAll('.filtro-rama')).every(cb => cb.checked);
    document.getElementById('filtroTodos').checked = allChecked;

    // Llamamos a applyFilters para aplicar todos los filtros y mostrar u ocultar resultados
    applyFilters();
}