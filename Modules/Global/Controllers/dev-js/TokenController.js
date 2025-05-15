jQuery(function () {
  const urlParams = new URLSearchParams(window.location.search);
  const token = urlParams.get('token');
  const type = urlParams.get('type') || 'autologin'; // default
  const email = urlParams.get('email');

  if (token && type && email) {
    $.ajax({
      url: './Modules/Global/Controllers/TokenValidator.php',
      type: 'GET',
      data: { token, type },
      dataType: 'json',
      success: function (res) {
        if (res.success) {
          Swal.fire({
            icon: 'success',
            title: res.title, //Titulo pers desde el back
            text: res.message, // Mensaje pers desde el back
            confirmButtonText: 'OK',
            confirmButtonColor: '#4CAF50',
            customClass: { popup: 'mi-popup-central' },
          }).then(() => {
            localStorage.setItem("abrirOffcanvas", "1");
            window.location.href = '/inicio';
          });

        } else if (res.code === 'already_verified') {
          Swal.fire({
            icon: 'warning',
            title: '¡Correo ya verificado!',
            text: res.message, // Mensaje personalizado desde PHP
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#f4b400',
            customClass: { popup: 'mi-popup-central' },
          }).then(() => {
            window.location.href = '/inicio';
          });
        } else if (res.code === 'error_mail') {
          Swal.fire({
            icon: 'error',
            title: 'Error al enviar el correo',
            text: res.message, // Mensaje personalizado desde PHP
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#f4b400',
            customClass: { popup: 'mi-popup-central' },
          }).then(() => {
            window.location.href = '/inicio';
          });

        } else if (res.code === 'expired') {
          mostrarTokenInvalido(res.message, email); // Usar el mensaje de expiración del PHP

        } else if (res.code === 'recover') {
          Swal.fire({
            title: 'Nueva contraseña',
            html: `
              <p>Introduce una nueva contraseña para <strong>${email}</strong></p>
              <input type="password" id="pass1" class="swal2-input" placeholder="Nueva contraseña">
              <input type="password" id="pass2" class="swal2-input" placeholder="Repite la contraseña">
              <div id="errorText" style="color:red; font-size: 0.9em; display:none;">Las contraseñas no coinciden</div>
              <div style="margin-top: 10px;">
                  <label><input type="checkbox" id="showPassword"> Mostrar contraseñas</label>
              </div>
            `,
            confirmButtonText: 'Guardar',
            focusConfirm: false,
            customClass: {
              popup: 'mi-popup-central'
            },
            preConfirm: () => {
              const pass1 = document.getElementById('pass1').value;
              const pass2 = document.getElementById('pass2').value;
          
              if (!pass1 || !pass2) {
                Swal.showValidationMessage('Debes completar ambos campos');
                return false;
              }
          
              if (pass1 !== pass2) {
                document.getElementById('errorText').style.display = 'block';
                return false;
              }
              Swal.showLoading();
              return pass1;
            }
          }).then((result) => {
            
            if (result.isConfirmed && result.value) {
              Swal.fire({
                title: 'Actualizando...',
                text: 'Por favor, espera mientras actualizamos tu contraseña.',
                allowOutsideClick: false, // Deshabilitar clics fuera del modal
                didOpen: () => {
                  Swal.showLoading(); // Mostrar el loading spinner
                }
              });

              
              $.ajax({
                type: 'POST',
                url: './Modules/Global/Models/UpdatePassword.php',
                data: {
                  correo: email,     
                  token: token,
                  password: result.value
                },
                success: function (response) {
                  if (response.success) {
                    Swal.fire({
                      title: 'Éxito',
                      text: 'Tu contraseña ha sido actualizada',
                      icon: 'success',
                      customClass: {
                        popup: 'mi-popup-central'
                      },
                      didClose: () => {
                        localStorage.setItem("abrirOffcanvas", "1");
                        window.location.href = '/inicio';
                      }
                    });
                  } else {
                    Swal.fire('Error', response.message || 'Ocurrió un error', 'error');
                  }
                },
                error: function (xhr) {
                  window.location.href = "/500";
                }
              });
            }
          });
          
          // Mostrar contraseñas cuando se marque la casilla de "Mostrar contraseñas"
          document.getElementById('showPassword').addEventListener('change', function() {
            const pass1 = document.getElementById('pass1');
            const pass2 = document.getElementById('pass2');
          
            if (this.checked) {
              pass1.type = 'text';
              pass2.type = 'text';
            } else {
              pass1.type = 'password';
              pass2.type = 'password';
            }
          });
          

        } else {
          mostrarTokenInvalido(res.message, email); // Mostrar mensaje de token no válido
        }

      },
      error: function () {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: 'Ha ocurrido un error al verificar el vínculo.',
          confirmButtonText: 'OK',
          customClass: { popup: 'mi-popup-central' },
        });
      }
    });
  }
});

function mostrarTokenInvalido(msg, email) {
  Swal.fire({
    icon: 'error',
    title: 'Token inválido',
    html: `
      <p>${msg}</p>
      <input id="correoReenvio" type="email" placeholder="Introduce tu correo" value="${email || ''}" class="swal2-input">
    `,
    confirmButtonText: 'Reenviar correo',
    customClass: {
      popup: 'mi-popup-central',
      icon: 'swal-icon-center'
    },
    preConfirm: () => {
      const correo = $('#correoReenvio').val();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i;

      if (!correo || !emailRegex.test(correo)) {
        Swal.showValidationMessage('Por favor introduce un correo válido y bien formado');
        return false;
      }

      // Enviamos al backend con los nuevos parámetros
      return $.ajax({
        url: './Modules/Global/Controllers/TokenValidator.php',
        type: 'GET',
        data: {
          type: 'resend_email',
          email: correo
        },
        dataType: 'json'
      }).then(res => {
        if (!res.success) {
          Swal.showValidationMessage(res.message || 'Error al reenviar');
        }
        return res;
      }).catch(() => {
        Swal.showValidationMessage('Error inesperado al contactar con el servidor');
      });
    }
  }).then(result => {
    if (result.isConfirmed && result.value?.success) {
      Swal.fire({
        icon: 'success',
        title: 'Correo reenviado',
        text: result.value.message || 'Te hemos enviado un nuevo correo de verificación.',
        confirmButtonText: 'OK',
        customClass: {
          popup: 'mi-popup-central',
          icon: 'swal-icon-center'
        }
      });
    }
  });
}