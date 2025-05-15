const pathsToBlock = [
    '/Modules',
    '/Public',
    '/Vendor',
    '/Models',
    '/Mail',
    '/Controllers',
    '/Config'
];

// Obtiene la URL actual
const currentPath = window.location.pathname;

// Revisa si el path actual contiene alguno de los bloqueados
const shouldRedirect = pathsToBlock.some(path => currentPath.startsWith(path));

// Si coincide, redirige
if (shouldRedirect) {
    window.location.href = '/404'; // Cambia esto a la página que desees
}

jQuery(function () {
    let urlFile = "/Models/cookieLogin.php";
    // Realizamos una llamada AJAX para verificar la sesión del usuario
    $.ajax({
        url: urlFile, // El archivo PHP que verifica la sesión
        type: 'GET',
        dataType: 'json', // espera una respuesta JSON
        success: function (response) {
            // Verificamos si la respuesta es correcta
            if (response.error) {
                // Si no hay sesión activa, cargo la página de login
                $('#userIframe').attr('src', '/login-register');
            } else {
                // Si la sesión está activa, carga la página para su menú
                $('#userIframe').attr('src', '/menu-perfil');
            }
        },
        error: function (xhr, status, error) {
            if (xhr.status === 404) {
                window.location.href = '/404';
            } else {
                window.location.href = "/500";
            }
        }
    });


$('#userIframe').on("load", function () {
    const iframe = $("#userIframe");
    let change = false;
    let mensaje = '';
    let tipo = 'info';
    let setTime = 3000;

    if (iframe.hasClass("login")) {
        mensaje = "Sesión iniciada";
        tipo = "success";
        iframe.removeClass("login");
        change = true;
    } else if (iframe.hasClass("logout")) {
        mensaje = "Sesión cerrada";
        tipo = "info";
        iframe.removeClass("logout");
        change = true;
    } else if (iframe.hasClass("failMail")) {
        mensaje = "No se pudo enviar el correo, revise sus datos";
        tipo = "warning";
        iframe.removeClass("failMail");
        change = true;
        setTime = 5000;
    }

    if (change) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: tipo,
            title: mensaje,
            showConfirmButton: false,
            timer: setTime,
            timerProgressBar: true,
            customClass: {
                popup: 'swal2-toast-custom toast-iframe'
            }
        });

        // Redirección tras 4 segundos si está en una ruta no permitida
        setTimeout(() => {
            const rutasProhibidas = [
                "asociados",
                "panel-circulares",
                "kraal",
                "usuarios"
            ];

            const urlActual = window.location.pathname.toLowerCase();

            const enRutaProhibida = rutasProhibidas.some(ruta => urlActual.includes(ruta));

            if (enRutaProhibida) {
                window.location.href = "/inicio";
            }
        }, 4000);
    }
});

});