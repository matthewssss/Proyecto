const $grid = $('.datosMultiple').isotope({
    itemSelector: '.mix',
    layoutMode: 'fitRows',
    getSortData: {
        nombre: '[data-nombre]',
        edad: '[data-edad] parseInt',
        unidad: '[data-unidad]'
    }
});

$.ajax({
    url: '../Controllers/Servlet.php',
    type: 'GET',
    data: {
        action: 'getCampoUsr',
        campo: 'idRol'
    },
    dataType: 'text',
    success: function (idResult) {
        if (idResult == 1) {
            $grid.isotope({ filter: '.mios' });
            $('#registros option[value="1"], #registros option[value="3"]').remove();
            $('#registros').prop('disabled', true);
        } else if (idResult > 1 && idResult < 5) {
            $grid.isotope({ filter: '.unidad' });
            $('#registros option[value="3"]').remove();
            $('#registros').prop('disabled', false);
        } else if (idResult == 5) {
            $grid.isotope({ filter: '.unidad' });
            $('#registros').prop('disabled', false);
        }
        toggleRamaOptions(false);
        aplicarFiltros();
    },
    error: function (xhr, status, error) {
        Swal.fire('Error', 'No se pudieron cargar los detalles.', 'error');
    }
});

function getFiltroClaseActual() {
    const valor = $('#registros').val();
    if (valor == '1') return '.unidad';
    if (valor == '2') return '.mios';
    return '*';
}

function aplicarFiltros() {
    const texto = $('#searchAsociado').val().toLowerCase();
    const filtroClase = getFiltroClaseActual();

    $grid.isotope({
        filter: function () {
            const $item = $(this);

            if (filtroClase !== '*' && !$item.is(filtroClase)) {
                return false;
            }

            if (!texto) return true;

            const nombre = $item.data('nombre')?.toString().toLowerCase() || '';
            const apellidos = $item.data('apellidos')?.toString().toLowerCase() || '';
            const edad = $item.data('edad')?.toString().toLowerCase() || '';
            const padreNombre = $item.data('padre-nombre')?.toString().toLowerCase() || '';
            const padreApellidos = $item.data('padre-apellidos')?.toString().toLowerCase() || '';
            const padreCorreo = $item.data('padre-correo')?.toString().toLowerCase() || '';
            const padreTel = $item.data('padre-telefono')?.toString().toLowerCase() || '';
            const unidad = $item.data('unidad')?.toString().toLowerCase() || '';

            return (
                nombre.includes(texto) ||
                apellidos.includes(texto) ||
                edad.includes(texto) ||
                padreNombre.includes(texto) ||
                padreApellidos.includes(texto) ||
                padreCorreo.includes(texto) ||
                padreTel.includes(texto) ||
                unidad.includes(texto)
            );
        }
    });

    setTimeout(() => {
        const visibles = $grid.data('isotope').filteredItems.length;
        $('#sinResultados').toggle(visibles === 0);
        $('#ordenar').prop('disabled', (visibles === 0));

        if (visibles === 0) {
            $('#ordenar option:first').text('Sin resultados para ordenar');
        } else {
            $('#ordenar option:first').text('Ordenar por:');
        }
    }, 100);
}

function toggleRamaOptions(show) {
    const options = $('#ordenar option[value="5"], #ordenar option[value="6"], #ordenar option[value="7"], #ordenar option[value="8"]');
    show ? options.show() : options.hide();
}

$('.rotate').on('click', function () {
    let card = $(this).closest('.card-body');
    card.find('.front, .back').toggleClass('d-none');
});

//TODO DETALLES
$('.detallesBtn').on('click', function (event) {
    let idAsociado = event.target.getAttribute('data-id');

    $.ajax({
        url: '../Controllers/Servlet.php',
        type: 'GET',
        data: {
            action: 'getDetallesAsociado',
            idAsociado: idAsociado
        },
        dataType: 'json',
        success: function (card) {
            if (card.error) {
                Swal.fire('Error', 'No se pudieron obtener los detalles.', 'error');
                return;
            }

            let detalles = card.msg;
            let detallesHTML = `
                <div style="text-align:left;">
                    <h5>Datos del Niño</h5>
                    <p><strong>Nombre:</strong> ${detalles.asociado.nombre}</p>
                    <p><strong>Apellidos:</strong> ${detalles.asociado.apellidos}</p>
                    <p><strong>Fecha de Nacimiento:</strong> ${detalles.asociado.fecha_nacimiento}</p>
                    <p><strong>Edad:</strong> ${detalles.asociado.edad} años</p>
                    <p><strong>Rama:</strong> ${detalles.asociado.unidad}</p>
                    <p><strong>Municipio:</strong> ${detalles.asociado.municipio || "No disponible"}</p>
                    <p><strong>Provincia:</strong> ${detalles.asociado.provincia || "No disponible"}</p>
                    <p><strong>Comunidad Autónoma:</strong> ${detalles.asociado.comunidad_autonoma || "No disponible"}</p>

                    <hr>

                    <h5>Datos del Padre</h5>
                    <p><strong>Nombre:</strong> ${detalles.padre.nombre}</p>
                    <p><strong>Apellidos:</strong> ${detalles.padre.apellidos}</p>
                    <p><strong>Correo:</strong> ${detalles.padre.correo}</p>
                    <p><strong>Teléfono:</strong> ${detalles.padre.telefono}</p>
                    <p><strong>Estado Civil:</strong> ${detalles.padre.estado_civil}</p>
                </div>
            `;

            Swal.fire({
                html: `
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h4 class="mb-0">Detalles del Asociado</h4>
                        <button type="button" id="btnCloseModal" class="btn-close" aria-label="Cerrar"></button>
                    </div>
                    ` 
                    + detallesHTML,
                width: '600px',
                confirmButtonText: 'Cerrar',
                customClass: {
                    popup: 'swal2-popup-custom'
                },
                didOpen: () => {
                    document.getElementById('btnCloseModal').addEventListener('click', () => {
                        Swal.close();
                    });
                }
            });
        },
        error: function () {
            Swal.fire('Error', 'No se pudieron cargar los detalles.', 'error');
        }
    });
});

$('#registros').on('change', function () {
    const valor = $(this).val();
    $('#searchAsociado').val('');
    $('#ordenar').val('0');
    $('#sinResultados').hide();

    if (valor == 1 || valor == 2) {
        toggleRamaOptions(false);
    } else {
        toggleRamaOptions(true);
    }

    aplicarFiltros();
});

$('#ordenar').on('change', function () {
    const valor = $(this).val();
    $('#sinResultados').hide();

    let sortBy = '';
    let sortAscending = true;

    switch (valor) {
        case '0':
            $('#closeFilters').hide();
            $('#searchAsociado').val("");
            $('#ordenar').val('0');
            aplicarFiltros();
            $grid.isotope({ sortBy: 'original-order' });
            return;
        case '1':
            sortBy = 'edad';
            sortAscending = false;
            break;
        case '2':
            sortBy = 'edad';
            sortAscending = true;
            break;
        case '3':
            sortBy = 'nombre';
            sortAscending = true;
            break;
        case '4':
            sortBy = 'nombre';
            sortAscending = false;
            break;
        case '5':
        case '6':
        case '7':
        case '8':
            const ramas = {
                '5': 'Lobatos',
                '6': 'Exploradores',
                '7': 'Pioneros',
                '8': 'Rutas'
            };
            const rama = ramas[valor];
            $grid.isotope({ filter: `[data-unidad="${rama}"]` });
            setTimeout(() => {
                const visibles = $grid.data('isotope').filteredItems.length;
                $('#sinResultados').toggle(visibles === 0);
            }, 100);
            $('#closeFilters').show();
            return;
    }

    $grid.isotope({ sortBy: sortBy, sortAscending: sortAscending });
    //aplicarFiltros();
    $('#closeFilters').show();
});

$('#searchAsociado').on('input', function () {
    let resetFilter = document.getElementById('closeFilters');
    const texto = $('#searchAsociado').val().toLowerCase() == "" ? resetFilter.style.display = 'none' : resetFilter.style.display = 'block';

    aplicarFiltros();
});

$('#closeFilters').on('click', function () {
    $('#ordenar').val('0');
    $('#searchAsociado').val("");
    $('#sinResultados').hide();
    document.getElementById('closeFilters').style.display = 'none';
    aplicarFiltros();
});

$('#resetSearchAsociado').on('click', function () {
    $('#searchAsociado').val("");

    const orden = $('#ordenar').val();

    if (orden == 0) document.getElementById('closeFilters').style.display = 'none';

    aplicarFiltros();
});


//TODO EDITAR
$('.editData').on('click', function (event) {
    let idAsociado = event.target.getAttribute('data-id');
    const Toast = Swal.mixin({
        toast: true,
        position: 'top',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    Swal.fire({
        html: `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h4 class="mb-0">Editar Datos</h4>
                    <button type="button" id="btnCloseModal" class="btn-close" aria-label="Cerrar"></button>
                </div>
                <div style="text-align: center; margin-top: 10px;">
                    <button type="button" class="btn btn-primary" id="guardarCambiosTop">Guardar</button>
                </div>
                <div id="editForm" style="text-align: left; padding: 10px;">Cargando datos...</div>
                `,
        showCancelButton: true,
        confirmButtonText: 'Guardar Cambios',
        cancelButtonText: 'Cancelar',
        width: '600px',
        customClass: {
            popup: 'swal-wide'
        },
        focusConfirm: false,
        didOpen: () => {
            document.getElementById('btnCloseModal').addEventListener('click', () => {
                Swal.close();
            });
            document.getElementById('guardarCambiosTop').addEventListener('click', () => {
                document.querySelector('.swal2-confirm')?.click();
            });

            const content = Swal.getHtmlContainer();
            content.querySelector('#editForm').innerHTML = cargarLoader();

            $.ajax({
                url: '../Controllers/Servlet.php',
                type: 'GET',
                data: {
                    action: 'getDetallesAsociado',
                    idAsociado: idAsociado
                },
                dataType: 'json',
                success: function (card) {
                    if (card.error) {
                        content.querySelector('#editForm').innerHTML = 'Error al cargar los detalles.';
                        return;
                    }

                    let d = card.msg;
                    let formHTML = `
                                        <h5>Datos del Niño</h5>
                                        <div class="mb-2">
                                            <label>Nombre:</label>
                                            <input type="text" class="form-control" id="inputNombre" value="${d.asociado.nombre}" required>
                                            <div class="invalid-feedback">El nombre es obligatorio</div>
                                        </div>
                                        <div class="mb-2">
                                            <label>Apellidos:</label>
                                            <input type="text" class="form-control" id="inputApellidos" value="${d.asociado.apellidos}" required>
                                            <div class="invalid-feedback">Los apellidos son obligatorios</div>
                                        </div>
                                        <div class="mb-2">
                                            <label>Fecha de Nacimiento:</label>
                                            <input type="date" class="form-control" id="inputFechaNacimiento" value="${d.asociado.fecha_nacimiento}" required>
                                            <div class="invalid-feedback" id="fechaErrorRama"></div>
                                        </div>
                                        <div class="mb-2">
                                            <label>Edad:</label>
                                            <input type="number" class="form-control" id="inputEdad" value="${d.asociado.edad}" disabled>
                                        </div>
                                        <div class="mb-2">
                                            <label>Rama:</label>
                                            <input type="text" class="form-control" id="inputUnidad" value="${d.asociado.unidad}" disabled>
                                        </div>
                                        <div class="mb-2">
                                            <label>Comunidad Autónoma</label>
                                            <select id="inputComunidad" class="form-control" required></select>
                                            <div class="invalid-feedback">Selecciona una comunidad</div>
                                        </div>
                                        <div class="mb-2">
                                            <label>Provincia</label>
                                            <select id="inputProvincia" class="form-control" required></select>
                                            <div class="invalid-feedback">Selecciona una provincia</div>
                                        </div>
                                        <div class="mb-3">
                                            <label>Municipio</label>
                                            <select id="inputMunicipio" class="form-control" required></select>
                                            <div class="invalid-feedback">Selecciona un municipio</div>
                                        </div>
                                    
                                        <hr>
                                        <h5>Datos del Padre</h5>
                                        <div class="mb-2">
                                            <label>Nombre:</label>
                                            <input type="text" class="form-control" id="inputPadreNombre" value="${d.padre.nombre}" required>
                                            <div class="invalid-feedback">El nombre del padre es obligatorio</div>
                                        </div>
                                        <div class="mb-2">
                                            <label>Apellidos:</label>
                                            <input type="text" class="form-control" id="inputPadreApellidos" value="${d.padre.apellidos}" required>
                                            <div class="invalid-feedback">Los apellidos del padre son obligatorios</div>
                                        </div>
                                        <div class="mb-2">
                                            <label>Correo:</label>
                                            <input type="email" class="form-control" id="inputPadreCorreo" value="${d.padre.correo}" required>
                                            <div class="invalid-feedback">Correo inválido</div>
                                        </div>
                                        <div class="mb-2">
                                            <label>Teléfono:</label>
                                            <input type="text" class="form-control" id="inputPadreTelefono" value="${d.padre.telefono}" required>
                                            <div class="invalid-feedback">El teléfono es obligatorio</div>
                                        </div>
                                        <div class="mb-2">
                                            <label>Estado Civil:</label>
                                            <input type="text" class="form-control" id="inputPadreEstadoCivil" value="${d.padre.estado_civil}" required>
                                            <div class="invalid-feedback">Este campo es obligatorio</div>
                                        </div>
                                    `;
                

                    content.querySelector('#editForm').innerHTML = formHTML;

                    // Cargar selects dinámicos
                    loadAddresses(d.asociado.comunidad_autonomaId, d.asociado.provinciaId, d.asociado.municipioId);

                    // Guardar datos iniciales para comparar luego
                    Swal.__initialValues = {
                        idAsociado: d.asociado.id_ins,
                        nombre: d.asociado.nombre,
                        apellidos: d.asociado.apellidos,
                        fecha_nacimiento: d.asociado.fecha_nacimiento,
                        edad: d.asociado.edad,
                        unidad: d.asociado.unidad,
                        comunidadId: d.asociado.comunidad_autonomaId,
                        provinciaId: d.asociado.provinciaId,
                        municipioId: d.asociado.municipioId,
                        padreId: d.padre.id,
                        padreNombre: d.padre.nombre,
                        padreApellidos: d.padre.apellidos,
                        padreCorreo: d.padre.correo,
                        padreTelefono: d.padre.telefono,
                        padreEstadoCivil: d.padre.estado_civil
                    };
                },
                error: () => {
                    content.querySelector('#editForm').innerHTML = 'Error al obtener los datos.';
                }
            });
        },
        preConfirm: () => {

            if (!validarFormularioEdit()) {
                Swal.showValidationMessage("Hay campos obligatorios no rellenados o inválidos");
                return false;
            }

            

            const i = Swal.__initialValues;
            const formData = {
                nombre: $('#inputNombre').val(),
                apellidos: $('#inputApellidos').val(),
                fecha_nacimiento: $('#inputFechaNacimiento').val(),
                edad: $('#inputEdad').val(),
                unidad: $('#inputUnidad').val(),
                comunidadId: $('#inputComunidad').val(),
                provinciaId: $('#inputProvincia').val(),
                municipioId: $('#inputMunicipio').val(),
                padreNombre: $('#inputPadreNombre').val(),
                padreApellidos: $('#inputPadreApellidos').val(),
                padreCorreo: $('#inputPadreCorreo').val(),
                padreTelefono: $('#inputPadreTelefono').val(),
                padreEstadoCivil: $('#inputPadreEstadoCivil').val()
            };

            const changes = {};
            for (let key in formData) {
                if (formData[key] !== String(i[key])) {
                    changes[key] = formData[key];
                }
            }

            if (Object.keys(changes).length === 0) {
                Toast.fire({
                    icon: 'info',
                    title: 'No se han detectado cambios'
                });
                return false;
            }

            return $.ajax({
                        url: '../Controllers/Servlet.php',
                        type: 'POST',
                        data: {
                            action: 'updateAsociadoYPadre',
                            asociado: {
                                idAsociado: i.idAsociado,
                                nombre: changes.nombre || i.nombre,
                                apellidos: changes.apellidos || i.apellidos,
                                fecha_nacimiento: changes.fecha_nacimiento || i.fecha_nacimiento,
                                edad: changes.edad || i.edad,
                                unidad: changes.unidad || i.unidad,
                                comunidadId: changes.comunidadId || i.comunidadId,
                                provinciaId: changes.provinciaId || i.provinciaId,
                                municipioId: changes.municipioId || i.municipioId
                            },
                            padre: {
                                idPadre: i.padreId,
                                nombre: changes.padreNombre || i.padreNombre,
                                apellidos: changes.padreApellidos || i.padreApellidos,
                                correo: changes.padreCorreo || i.padreCorreo,
                                telefono: changes.padreTelefono || i.padreTelefono,
                                estadoCivil: changes.padreEstadoCivil || i.padreEstadoCivil
                            }
                        },
                        dataType: 'json'
                    }).then((res) => {
                        if (!res.success) throw new Error("Error en actualización");
                        return res;
                    }).catch((err) => {
                        Swal.showValidationMessage(`Error: ${err.message}`);
                    });
        }
    }).then((result) => {
        if (result.isConfirmed && result.value && result.value.success) {
            smartReload("edit");
        }
    });
    

    function validarFormularioEdit() {
        let valido = true;
    
        const campos = [
            'inputNombre',
            'inputApellidos',
            'inputFechaNacimiento',
            'inputComunidad',
            'inputProvincia',
            'inputMunicipio',
            'inputPadreNombre',
            'inputPadreApellidos',
            'inputPadreCorreo',
            'inputPadreTelefono',
            'inputPadreEstadoCivil'
        ];
    
        campos.forEach(id => {
            const input = document.getElementById(id);
            if (!input) return;
            
            const valor = input.value.trim();
    
            // Validar email con formato si es el correo
            if (id === 'inputPadreCorreo') {
                const esValido = /^[^@]+@[^@]+\.[a-z]{2,}$/.test(valor);
                if (!esValido) {
                    input.classList.add('is-invalid');
                    valido = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            } else {
                if (!valor) {
                    input.classList.add('is-invalid');
                    valido = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            }
        });
    
        return valido;
    }
    
    
});

function cargarLoader() {
    return `
        <div id="loaderEdit">
            <div class="leap-frog">
                <div class="leap-frog__dot"></div>
                <div class="leap-frog__dot"></div>
                <div class="leap-frog__dot"></div>
            </div>
        </div>
    `;
}

function debounce(func, delay) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(this, args), delay);
    };
}

function loadAddresses(comunidad, provincia, municipio) {
    const configSelect2 = {
        allowClear: true,
        dropdownCssClass: 'select2-dropdown-up',
        minimumResultsForSearch: 0, // Siempre visible
        width: '100%',
        dropdownAutoWidth: true,
        position: 'absolute' // Esto asegura que el dropdown esté bien posicionado
    };

    $('#inputComunidad').select2({
        placeholder: "Selecciona una Comunidad Autónoma",
        ...configSelect2
    });

    $('#inputProvincia').select2({
        placeholder: "Selecciona una Provincia",
        ...configSelect2
    });

    $('#inputMunicipio').select2({
        placeholder: "Selecciona un Municipio",
        ...configSelect2
    });

    $('#inputComunidad').on('change', debounce(function () {
        const comunidadId = $(this).val();

        if (comunidadId) {
            loadProvincias(comunidadId);
            $('#inputMunicipio').empty().trigger('change.select2');
        } else {
            // Si se borra la comunidad, volver a cargar todas
            loadComunidades();
            $('#inputProvincia').empty().trigger('change.select2');
            $('#inputMunicipio').empty().trigger('change.select2');
        }
    }, 500));

    $('#inputProvincia').on('change', debounce(function () {
        const provinciaId = $(this).val();
        const comunidadId = $('#inputComunidad').val();

        if (provinciaId) {
            loadMunicipios(provinciaId);
        } else if (comunidadId) {
            // Si se borra la provincia, recargar provincias de la comunidad seleccionada
            loadProvincias(comunidadId);
            $('#inputMunicipio').empty().trigger('change.select2');
        } else {
            // No hay comunidad seleccionada, limpiar todo
            $('#inputMunicipio').empty().trigger('change.select2');
        }
    }, 500));



    loadComunidades(comunidad);
    loadProvincias(comunidad, provincia);
    loadMunicipios(provincia, municipio);
    document.getElementById('editForm').style.display = 'block';
}

function loadComunidades(comunidadId = null) {
    $.ajax({
        url: "../Controllers/Servlet.php?action=getComunidades",
        type: "GET",
        dataType: "json",
        success: function (data) {
            if (data.error) {
                console.log("No se encontraron comunidades.");
            } else {
                var $comunidadSelect = $('#inputComunidad');
                $comunidadSelect.empty(); // Limpiar las opciones anteriores
                $comunidadSelect.append('<option value="" disabled selected>Selecciona una comunidad autónoma</option>');

                // Agregar nuevas opciones al select
                $.each(data.msg, function (index, comunidad) {
                    const selected = comunidad.id == comunidadId ? 'selected' : '';
                    $comunidadSelect.append('<option value="0' + comunidad.id + '" ' + selected + '>' + comunidad.nombre + '</option>');
                });
            }
        },
        error: function (xhr, status, error) {
            window.location.href = "/500";
        }
    });
}

function loadProvincias(comunidadId = null, provinciaId = null) {
    $.ajax({
        url: "../Controllers/Servlet.php?action=getProvincias&comunidadId=" + comunidadId,
        type: "GET",
        dataType: "json",
        success: function (data) {
            if (data.error) {
                console.log("No se encontraron provincias.");
            } else {
                var $provinciaSelect = $('#inputProvincia');
                $provinciaSelect.empty(); // Limpiar las opciones anteriores
                $provinciaSelect.append('<option value="0" disabled selected>Selecciona una provincia</option>');

                // Agregar nuevas opciones al select
                $.each(data.msg, function (index, provincia) {
                    const selected = provincia.id == provinciaId ? 'selected' : '';
                    $provinciaSelect.append('<option value="' + provincia.id + '" ' + selected + '>' + provincia.nombre + '</option>');
                });
            }
        },
        error: function (xhr, status, error) {
            window.location.href = "/500";
        }
    });
}

function loadMunicipios(provinciaId = null, municipioId = null) {
    $.ajax({
        url: "../Controllers/Servlet.php?action=getMunicipios&provinciaId=" + provinciaId,
        type: "GET",
        dataType: "json",
        success: function (data) {
            if (data.error) {
                console.log("No se encontraron municipios.");
            } else {
                var $municipioSelect = $('#inputMunicipio');
                $municipioSelect.empty(); // Limpiar las opciones anteriores
                $municipioSelect.append('<option value="0" disabled selected>Selecciona un municipio</option>');

                // Agregar nuevas opciones al select
                $.each(data.msg, function (index, municipio) {
                    const selected = municipio.id == municipioId ? 'selected' : '';
                    $municipioSelect.append('<option value="' + municipio.id + '" ' + selected + '>' + municipio.nombre + '</option>');
                });
            }
        },
        error: function (xhr, status, error) {
            window.location.href = "/500";
        }
    });
}

//TODO DELETE
$('.deleteData').on('click', function (event) {
    let idAsociado = event.target.getAttribute('data-id');
    let idPadre = event.target.getAttribute('data-idP');

    $.ajax({
        url: '../Controllers/Servlet.php',
        type: 'GET',
        data: {
            action: 'getCampo',
            campo: 'nombreCompleto',
            idAsociado: idAsociado,
        },
        dataType: 'text',
        success: function (nombreCompleto) {
            Swal.fire({
                title: '¿Eliminar Asociado?',
                html: `¿Estás seguro de que deseas eliminar a <strong>${nombreCompleto}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                customClass: {
                    popup: 'swal2-popup-custom'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../Controllers/Servlet.php',
                        type: 'POST',
                        data: {
                            action: 'deleteAsociado',
                            idAsociado: idAsociado,
                            idPadre: idPadre
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                smartReload("delete"); // Recargar la página para mostrar los cambios y almacenar delete para el msg
                            } else {
                                Swal.fire('Error', 'No se pudo eliminar el asociado.', 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('Error', 'Error de red al intentar eliminar.', 'error');
                        }
                    });
                }
            });
        },
        error: function () {
            Swal.fire('Error', 'No se pudo obtener el nombre del asociado.', 'error');
        }
    });
});

//Almacenar mensaje tras completar edit o delete

function smartReload(action = '') {
    localStorage.setItem("toastAction", action); // "edit" o "delete"
    location.reload();
}

//Muestra mensaje almacenado
jQuery(function () {
    const action = localStorage.getItem("toastAction");
    if (action) {
        let mensaje = '';
        let icon = 'success';

        if (action === 'edit') {
            mensaje = 'Datos actualizados correctamente';
        } else if (action === 'delete') {
            mensaje = 'Asociado eliminado correctamente';
        } else if (action === 'new') {
            mensaje = 'Asociado creado correctamente';
        }

        if (mensaje) {
            Swal.fire({
                toast: true,
                position: 'top',
                icon: icon,
                title: mensaje,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }

        localStorage.removeItem("toastAction");
    }
});
