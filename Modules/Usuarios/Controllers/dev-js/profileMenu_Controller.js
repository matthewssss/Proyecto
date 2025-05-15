jQuery(function () {
    cargarBotones();
    /* LOG OUT */
    $("#id_logout").on("click", cerrarSesion);

    sacarCampo();
});


function cambiarIframe(nuevaURL) {
    let iframe = window.parent.document.getElementById("userIframe");

    iframe.style.scale = "0";
    iframe.src = nuevaURL;
}

function cerrarSesion() {
    $.ajax({
        url: "../Controllers/Servlet.php", //TODO puede fallar ruta CUIDADO
        type: "DELETE",
        data: JSON.stringify({ action: "logout" }),
        dataType: "json",
        success: function(response) {
            if (!response.error) {
                let isAuthenticated = true
                if (isAuthenticated) {
                    window.parent.document.getElementById("userIframe").src = "/login-register";
                    window.parent.document.getElementById("userIframe").classList.add("logout");
                }
            } else {
                alert("Error logging out: " + response.message);
            }
        },
        error: function(error) {
            window.top.location.href = "/500";
        }
    });
}

function sacarCampo () {
    $.ajax({
        url: "../Controllers/Servlet.php",
        type: "GET",
        data: {
            action: "campo",
            campoSacar: "nombre"
        },
        dataType: "json", // Expecting JSON response
        success: function (response) {
          $("#nombreUsr").text(response.value);
        },
        error: function (error) {
            window.top.location.href = "/500";
        },
    });
}

function cargarBotones () {
    $.ajax({
        url: "../Controllers/Servlet.php",
        type: "GET",
        data: {
            action: "getButtons"
        },
        dataType: "json", // Expecting JSON response
        success: function (response) {
          $("#id_listaBotones").html(response.menu);
          cargarEventos();
        },
        error: function (error) {
            window.top.location.href = "/500";
        },
    });
}

function cargarEventos () {
    $("#profile").on("click", function () {
        loadPageEdit();
    });

    $("#asociados").on("click", function () {
        window.top.location.href = "/asociados";
    });

    $("#kraal").on("click", function () {
        window.top.location.href = "/kraal";
    });

    $("#circulares").on("click", function () {
        window.top.location.href = "/panel-circulares";
    });

    $("#usuarios").on("click", function () {
        window.top.location.href = "/usuarios";
    });
}

function loadPageEdit() {
    $.ajax({
        url: "../Controllers/Servlet.php",
        type: "GET",
        data: {
            action: "editData"
        },
        dataType: "json", // Esperamos una respuesta en JSON
        success: function (response) {
            // Rellenamos el formulario con los datos obtenidos
            $('#nombre').val(response.nombre);
            $('#apellidos').val(response.apellidos);
            $('#correo').val(response.correo);
            $('#telefono').val(response.tel);

            $('.updateData').css('left', '0%');
        },
        error: function (error) {
            window.top.location.href = "/500";
        },
    });
}

$('#backArrow').on('click', (function() {
    $('.updateData').css('left', '-120%');
}));

$('#changeData').on('click', (function(e) { //TODO validar
    e.preventDefault();
    if ($('#editForm').valid()) {
        $.ajax({
            url: "../Controllers/Servlet.php",
            type: "POST",
            data: {
                action: "updateData",
                profile: true,
                nombre: $('#nombre').val(),
                apellidos: $('#apellidos').val(),
                correo: $('#correo').val(),
                telefono: $('#telefono').val(),
                password: $('#password').val(),
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Datos actualizados correctamente',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
                    $('.updateData').css('left', '-120%');
                    sacarCampo();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al actualizar los datos',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            },            
        
            error: function (error) {
                window.top.location.href = "/500";
            },
        }); 
    }
}));