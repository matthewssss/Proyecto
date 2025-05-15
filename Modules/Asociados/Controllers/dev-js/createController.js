/* Variables globales  */
let comunidadId = null;
let provinciaId = null;

jQuery(function () {
    loadFormMovement();
    //Cargar datos de cada comunidad/ciudad
    cargarSelectQuery();
    loadComunidades();
    /* BOTON GUARDAR */
    cargarEvento();
});

function loadFormMovement() {
    var navListItems = $('div.setup-panel div a'),
        allWells = $('.setup-content'),
        allNextBtn = $('.nextBtn'),
        allPrevBtn = $('.prevBtn');

    allWells.hide();

    navListItems.on("click", function (e) {
        e.preventDefault();
        var $target = $($(this).attr('href')),
            $item = $(this);

        if (!$item.hasClass('disabled')) {
            navListItems.removeClass('btn-primary').addClass('btn-default');
            navListItems.parent().find('p').removeClass('active-step');
            $item.addClass('btn-primary');
            $item.parent().find('p').addClass('active-step');
            allWells.hide();
            $target.show();
            $target.find('input:eq(0)').focus();
        }
    });

    allPrevBtn.on("click", function(){
        var curStep = $(this).closest(".setup-content"),
            curStepBtn = curStep.attr("id"),
            prevStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().prev().children("a");

        // Keep the completed-step class on the current button when going back
        $('div.setup-panel div a[href="#' + curStepBtn + '"]').addClass('completed-step');
        prevStepWizard.removeAttr('disabled').trigger('click');
    });

    allNextBtn.on("click", function(){
        var curStep = $(this).closest(".setup-content"),
            curStepBtn = curStep.attr("id"),
            nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
            curInputs = curStep.find("input[type='text'],input[type='url'],input[type='email'],input[type='date'],select"),
            isValid = true;

        $(".form-group").removeClass("has-error");
        for (var i = 0; i < curInputs.length; i++) {
            if (!curInputs[i].validity.valid) {
                isValid = false;
                $(curInputs[i]).closest(".form-group").addClass("has-error");
            }
        }

        if ($("#asociadoForm").valid()) {
            // Add completed class to current step button
            $('div.setup-panel div a[href="#' + curStepBtn + '"]').addClass('completed-step');
            nextStepWizard.removeAttr('disabled').trigger('click');
        }
    });

    // Trigger first step
    $('div.setup-panel div a.btn-primary').trigger('click');
}

function cargarSelectQuery() {
    /* Cargar select dinamicos */
    $('#comunidad_autonoma').select2({
        placeholder: "Selecciona una Comunidad Autónoma",
        allowClear: true,
        dropdownCssClass: 'custom-dark-dropdown',
        containerCssClass: 'custom-dark-container'
    });

    $('#provincia').select2({
        placeholder: "Selecciona una Provincia",
        allowClear: true,
        dropdownCssClass: 'custom-dark-dropdown',
        containerCssClass: 'custom-dark-container'
    });

    $('#municipio').select2({
        placeholder: "Selecciona un Municipio",
        allowClear: true,
        dropdownCssClass: 'custom-dark-dropdown',
        containerCssClass: 'custom-dark-container'
    });

    /* Cargar eventos escucha */
    $('#comunidad_autonoma').on('change', function() {
        setTimeout(function() {
            comunidadId = $('#comunidad_autonoma').val();
            if (comunidadId != 0) { 
                loadProvincias(comunidadId);
            }
        }
        , 500); //Pequeño retardo porque esta escribiendo y no hacer una carga todo el rato
    });
    $('#provincia').on('change', function() {
        setTimeout(function() {
            provinciaId = $('#provincia').val();
            if (provinciaId != 0) {
                loadMunicipios(provinciaId);
            }
        }
        , 500); //Pequeño retardo porque esta escribiendo y no hacer una carga todo el rato
    });
}

function loadComunidades() {
    $.ajax({
        url: "../Controllers/Servlet.php?action=getComunidades",
        type: "GET",
        dataType: "json",
        success: function(data) {
            if (data.error) {
                console.log("No se encontraron comunidades.");
                console.log(data);
            } else {
                var $comunidadSelect = $('#comunidad_autonoma');
                $comunidadSelect.empty(); // Limpiar las opciones anteriores
                $comunidadSelect.append('<option value="" disabled selected>Selecciona una comunidad autónoma</option>');

                // Agregar nuevas opciones al select
                $.each(data.msg, function(index, comunidad) {
                    $comunidadSelect.append('<option value="0' + comunidad.id + '">' + comunidad.nombre + '</option>');
                });
            }
        },
        error: function(xhr, status, error) {
            //window.top.location.href = "/500";
        }
    });
}

function loadProvincias() {
    $.ajax({
        url: "../Controllers/Servlet.php?action=getProvincias&comunidadId=" + comunidadId,
        type: "GET",
        dataType: "json",
        success: function(data) {
            if (data.error) {
                console.log("No se encontraron provincias.");
                console.log(data);
            } else {
                var $provinciaSelect = $('#provincia');
                $provinciaSelect.empty(); // Limpiar las opciones anteriores
                $provinciaSelect.append('<option value="0" disabled selected>Selecciona una provincia</option>');

                // Agregar nuevas opciones al select
                $.each(data.msg, function(index, provincia) {
                    $provinciaSelect.append('<option value="' + provincia.id + '">' + provincia.nombre + '</option>');
                });
            }
        },
        error: function(xhr, status, error) {
            //window.top.location.href = "/500";
        }
    });
}

function loadMunicipios () {
    $.ajax({
        url: "../Controllers/Servlet.php?action=getMunicipios&provinciaId=" + provinciaId,
        type: "GET",
        dataType: "json",
        success: function(data) {
            if (data.error) {
                console.log("No se encontraron municipios.");
                console.log(data);
            } else {
                var $municipioSelect = $('#municipio');
                $municipioSelect.empty(); // Limpiar las opciones anteriores
                $municipioSelect.append('<option value="0" disabled selected>Selecciona un municipio</option>');

                // Agregar nuevas opciones al select
                $.each(data.msg, function(index, municipio) {
                    $municipioSelect.append('<option value="' + municipio.id + '">' + municipio.nombre + '</option>');
                });
            }
        },
        error: function(xhr, status, error) {
            //window.top.location.href = "/500";
        }
    });
}

function cargarEvento (e) {
    const $tooltip = $('#tooltipSimulado');
    const $boton = $('#guardarDatos');

    // Mostrar errores en hover o al mover el ratón (escritorio)
    $boton.on('mouseenter mousemove', function (e) {
        mostrarErrores(e);
    });

    $boton.on('mouseleave', function () {
        $tooltip.hide();
    });

    // Validación en clic (móvil y escritorio)
    $boton.on('click', function (e) {
        e.preventDefault(); // Evitar el envío del formulario por defecto
        const errores = cargarMensajes();
        if (errores.length > 0) {
            e.preventDefault(); // Evitar el envío
            let erroresHTML = "<b>Errores detectados:</b><br>" + errores.join("<br>");
            
            // Mostrar el tooltip donde se hizo clic
            $tooltip.html(erroresHTML).show().css({
                top: (27) + 'rem',
                left: (3) + 'rem'
            });

            return false;
        }

        insertData();
    });


    function mostrarErrores(e) {
        const errores = cargarMensajes();
        if (errores.length > 0) {
            let erroresHTML = "<b>Errores detectados:</b><br>" + errores.join("<br>");
            $tooltip.html(erroresHTML).show().css({
                top: (e.pageY - $tooltip.outerHeight() - 10) + 'px',
                left: (e.pageX - 10) + 'px'
            });
        } else {
            $tooltip.hide();
        }
    }
    
    function insertData() {

        Swal.fire({
            title: 'Guardando cambios...',
            text: 'Por favor, espera un momento.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });

        const formData = {
            action: "insertData",
            // Personal data
            nombre: $('#nombre').val(),
            apellidos: $('#apellidos').val(),
            dni: $('#DNI').val(),
            fecha_nacimiento: $('#fecha_nacimiento').val(),
            edad: $('#edad').val(),
            unidad: $('#unidad').val(),
            
            // Location data
            //cp: $('#cp').val(),
            municipio: $('#municipio').val(),
            provincia: $('#provincia').val(),
            comunidad_autonoma: $('#comunidad_autonoma').val(),
            
            // Parent/Guardian data
            nombre_padre: $('#nombre_padre').val(),
            apellidos_padre: $('#apellidos_padre').val(),
            dni_padre: $('#dni_padre').val(),
            correo_padre: $('#correo_padre').val(),
            telefono_padre: $('#telefono_padre').val(),
            estado_civil: $('#estado_civil').val()
        };
    
        $.ajax({
            url: "../Controllers/Servlet.php",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(response) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('exampleModal'));
                if (modal) modal.hide();

                if (!response.error) {
                    localStorage.setItem("toastAction", 'new');
                    window.parent.location.reload();
                } else {
                    Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: 'error',
                        title: response.message || 'Ha ocurrido un error',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
                
            },
            error: function(error) {
                window.top.location.href = "/500";
            }
        });
    }
}

function cargarMensajes () {
    let errores = [];

    // Definir variables con operador ternario y agregar errores si están vacíos
    let nombre = $('#nombre').val().trim() ? $('#nombre').val().trim() : errores.push("Nombre vacío");
    let apellidos = $('#apellidos').val().trim() ? $('#apellidos').val().trim() : errores.push("Apellidos vacío");
    let dni = $('#DNI').val().trim() ? $('#DNI').val().trim() : errores.push("DNI vacío");
    let fecha_nacimiento = $('#fecha_nacimiento').val().trim() ? $('#fecha_nacimiento').val().trim() : errores.push("Fecha de nacimiento vacía");
    let edad = $('#edad').val().trim() ? $('#edad').val().trim() : errores.push("Edad vacía");
    let unidad = $('#unidad').val().trim() ? $('#unidad').val().trim() : errores.push("Unidad vacía");
    
    let municipio = $('#municipio').val() != 0 ? $('#municipio').val() : errores.push("Municipio vacío");
    let provincia = $('#provincia').val() != 0 ? $('#provincia').val() : errores.push("Provincia vacía");
    let comunidad_autonoma = $('#comunidad_autonoma').val() != 0 ? $('#comunidad_autonoma').val() : errores.push("Comunidad Autónoma vacía");
    
    let nombre_padre = $('#nombre_padre').val().trim() ? $('#nombre_padre').val().trim() : errores.push("Nombre del padre vacío");
    let apellidos_padre = $('#apellidos_padre').val().trim() ? $('#apellidos_padre').val().trim() : errores.push("Apellidos del padre vacío");
    let dni_padre = $('#dni_padre').val().trim() ? $('#dni_padre').val().trim() : errores.push("DNI del padre vacío");
    let correo_padre = $('#correo_padre').val().trim() ? $('#correo_padre').val().trim() : errores.push("Correo del padre vacío");
    let telefono_padre = $('#telefono_padre').val().trim() ? $('#telefono_padre').val().trim() : errores.push("Teléfono del padre vacío");
    let estado_civil = $('#estado_civil').val().trim() ? $('#estado_civil').val().trim() : errores.push("Estado civil vacío");
    
    return errores;
}