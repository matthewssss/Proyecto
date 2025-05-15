jQuery(function () {
    /* LOGIN Y LOGUP  BOTONES INICIALES*/
    cargarBotones();
  
    /* LOGIN Y LOGUP  BOTONES FINALES*/
    $("#id_login").on("click", function (e) {
      validarEmail("login", e);
    });
    $("#id_logup").on("click", function (e) {
      validarEmail("signup", e);
    });
    $("#id_forgot_psw").on("click", function (e) {
      validarEmail("forgot", e);
    });
  
    /* LOGIN PASSWORD */
    document
      .getElementById("toggle-password")
      .addEventListener("change", showPasswordLogIn);
    document
      .getElementById("id_psw0")
      .addEventListener("input", showPasswordValue);
  
    /* LOGUP PASSWORDS */
    // Al cambiar el checkbox, mostramos u ocultamos los recuadros
    document
      .getElementById("toggle-password-logup")
      .addEventListener("change", showPasswordLogUp);
    // Actualiza el contenido en cada input (pero no cambia la visibilidad)
    document
      .getElementById("id_psw1")
      .addEventListener("input", updatePopupPasswords);
    document
      .getElementById("id_psw2")
      .addEventListener("input", updatePopupPasswords);
  
    /* Contraseña olvidada */
    $("#id_forgot").on("click", mostrarContraOlvidada);
  });
  
  /*
  ------------------------------------------
  FUNCIONES
  ------------------------------------------
  */
  
  function cargarBotones() {
    //PODER CAMBIAR DE LOG IN/UP
    $(".form")
      .find("input, textarea")
      .on("keyup blur focus", function (e) {
        var $this = $(this);
        e.preventDefault();
        label = $this.prev("label");
        if (e.type === "keyup") {
          if ($this.val() === "") {
            label.removeClass("active highlight");
          } else {
            label.addClass("active highlight");
          }
        } else if (e.type === "blur") {
          if ($this.val() === "") {
            label.removeClass("active highlight");
          } else {
            label.removeClass("highlight");
          }
        } else if (e.type === "focus") {
          if ($this.val() === "") {
            label.removeClass("highlight");
          } else if ($this.val() !== "") {
            label.addClass("highlight");
          }
        }
      });
  
    $(".tab a").on("click", function (e) {
      clearForm(e);
      e.preventDefault();
      if ($(this)[0].id == "goBack") {
        restoreLinks();
        return;
      }
  
      $(this).parent().addClass("active");
      $(this).parent().siblings().removeClass("active");
  
      target = $(this).attr("href");
  
      $(".tab-content > div").not(target).hide();
  
      $(target).fadeIn(600);
    });
  }
  
  /* RECOGER DATOS DE CADA FORM */
  //Verificar que el mail existe en la bd antes de hacer nada
  /* FUNCIONES */
  function validarEmail(context, event) { //TODO VALIDAR MAILS
    Swal.fire({
      title: 'Cargando...',
      text: 'Por favor espera',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    event.preventDefault();
    let email;
    let validateForm = false;
  
    //Dependiendo del contexto agrego un mail u otro
    //Y como se que boton clica miro si el form es correcto
    if (context === "signup") {
      email = document.getElementById("id_email_logup").value;
      validateForm = $("#signin").valid();
    } else if (context === "forgot") {
      email = document.getElementById("id_email_forgot").value;
      validateForm = $("#resetPassword").valid();
    } else {
      email = document.getElementById("id_email").value;
      validateForm = $("#loginForm").valid();
    }
    if (validateForm) {
      $.ajax({
        url: "../Controllers/Servlet.php?action=checkEmail&email=" + email + "&recovery=" + context,
        type: "GET",
        dataType: "json",
        success: function (response) {
          if (context === "login" || context === "forgot") {
            if (response.exists) {
              if (context === "login") {
                loginUsr();
              } else {
                forgotPassword(response);
              }
            } else {
              $(".errorLog").text(response.message);
              showSweetToast("warning", "Correo electrónico no encontrado"); // VALIDACIÓN EMAIL - Not found
            }
          } else if (context === "signup") {
            if (!response.exists) {
              signInUsr(event);
            } else {
              $(".errorLog").text("Correo electronico ya registrado");
              showSweetToast("warning", "Correo electrónico ya registrado"); // VALIDACIÓN EMAIL - Ya registrado
            }
          }
        },
        error: function (error) {
          showSweetToast("error", "Error en la verificación del email"); // VALIDACIÓN EMAIL - Error
        },
  
      });
    }
  
  }
  
  // Función para enviar los datos del formulario de inicio de sesión
  function loginUsr() { //TODO LOGIN

    const loginEmail = document.getElementById("id_email").value;
    const loginPassword = document.getElementById("id_psw0").value;
  
    $.ajax({
      url: "../Controllers/Servlet.php",
      type: "GET",
      data: {
        action: "login",
        email: loginEmail,
        password: loginPassword,
      },
      dataType: "json",
      success: function(response) {
        if (response.error) {
          $(".errorLog").text(response.message);
          showSweetToast("error", response.message);  // Mostramos mensaje de error si lo hay
        } else {
          if (response.status === 'factor') {
            mostrarModalDobleFactor();  // Si es necesario el doble factor, mostramos el modal
          } else {
            // Login exitoso, redirigir al perfil del usuario
            let url = "/Modules/Usuarios/View/";
            window.parent.document.getElementById("userIframe").src = url + "profileMenu.html";
          }
        }
      },
      error: function(error) {
        window.top.location.href = "/500";
      }
    });
  }
  
  function mostrarModalDobleFactor() {
    Swal.fire({
      title: 'Introduce el código de verificación',
      input: 'text',
      inputPlaceholder: 'Código de 6 dígitos',
      inputAttributes: {
        maxlength: 6,  // Limitar a 6 caracteres
        minlength: 6   // Asegurarnos de que sea al menos 6 caracteres
      },
      showCancelButton: true,
      confirmButtonText: 'Verificar',
      cancelButtonText: 'Cancelar',
      customClass: {
        popup: 'mi-popup-central'
      },
      preConfirm: (codigo) => {
        // Verificar si el código tiene exactamente 6 dígitos
        if (!codigo) {
          Swal.showValidationMessage('Por favor, ingresa un código');
          return false;
        }
  
        if (!/^\d{6}$/.test(codigo)) {
          Swal.showValidationMessage('El código debe ser de 6 dígitos');
          return false;
        }
  
        // Enviar el código para verificarlo
        return $.ajax({
          url: "../Controllers/Servlet.php", 
          type: 'POST',
          data: {
            action: "login2",
            codigo: codigo
          },
          dataType: 'json',
          success: function(data) {
            if (!data.error) {
              // Si el código es correcto, redirigir al usuario
              Swal.fire({
                icon: 'success',
                title: data.message,
                confirmButtonText: 'OK',
              }).then(() => {
                let url = "/Modules/Usuarios/View/";
                window.parent.document.getElementById("userIframe").src = url + "profileMenu.html";
                window.parent.document.getElementById("userIframe").classList.add("login");
              });
            } else {
              // Si el código es incorrecto
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonText: 'Reintentar',
              });
            }
          },
          error: function(xhr, status, error) {
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: 'Ha ocurrido un error al verificar el código.',
              confirmButtonText: 'OK',
            });
          }
        });
      }
    });
  }
  
  function cambiarIframe(nuevaURL) {
    let iframe = window.parent.document.getElementById("userIframe");
  
    iframe.style.scale = "0";
    iframe.src = nuevaURL;
  }
  
  function signInUsr(e) { //TODO SIGN IN
    // Variables para el formulario de registro
    const signupEmail = document.getElementById("id_email_logup").value;
    const signupName = document.getElementById("id_name").value;
    const signupLastName = document.getElementById("id_apellido").value;
    const signupPassword = document.getElementById("id_psw1").value;
    const signupTlf = document.getElementById("id_tlf").value;
    $.ajax({
      url: "../Controllers/Servlet.php",
      type: "POST",
      data: {
        action: "signin",
        name: signupName,
        lastname: signupLastName,
        email: signupEmail,
        tlf: signupTlf,
        pass: signupPassword,
      },
      dataType: "json", // Expecting JSON response
      success: function (response) {
        if (!response.error) {
          showSweetToast("success", response.message); // SIGN IN - Success
          clearForm(e);
          goBackToLogin();
        } else {
          $(".errorLog").text(response.message);
          showSweetToast("error", response.message); // SIGN IN - Error
        }
      },
  
      error: function (error) {
        window.top.location.href = "/500";
      },
    });
  }
  
  function goBackToLogin() {
    // Primero, elimina la clase 'active' de todos los divs
    $("#login, #signup, #forgot").removeClass("active");
  
    // Luego, agrega la clase 'active' al div de login
    $("#login").addClass("active");
  
    // Opcionalmente, si usas un sistema de tabs, también puedes ocultar los otros divs
    $("#signup, #forgot").hide();
    $("#login").fadeIn(600); // Muestra el div de login con animación
  }
  
  function showSweetToast(type = 'success', message = 'Operación realizada') {
    Swal.fire({
      toast: true,
      position: 'top',
      icon: type,
      title: message,
      showConfirmButton: false,
      timer: 3500,
      timerProgressBar: true,
      customClass: {
        popup: 'swal2-toast-custom'
      },
      didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
      }
    });
  }
  
  function forgotPassword() {
    Swal.fire({
      icon: 'success',
      title: 'Correo enviado',
      text: 'Te hemos enviado un email con el código para restablecer tu contraseña. Revisa tu bandeja de entrada y también la carpeta de spam.',
      confirmButtonText: 'Entendido',
      confirmButtonColor: '#6a1b9a',
      customClass: {
        popup: 'swal2-toast-custom'
      },
    });  
  }
  
  /**PASSWORD EN EL LOGIN */
  // Función para mostrar la contraseña en el recuadro
  function showPasswordLogIn(e) {
    e.preventDefault();
    const displayBox = document.getElementById("password-display");
    if (this.checked) {
      displayBox.classList.add("show");
    } else {
      displayBox.classList.remove("show");
    }
  }
  
  function showPasswordValue(e) {
    e.preventDefault();
    const displayBox = document.getElementById("password-display");
    displayBox.textContent = this.value;
  }
  
  /* PASSWORD EN EL LOGUP */
  // Función para actualizar el contenido de ambos recuadros en tiempo real
  function updatePopupPasswords(e) {
    e.preventDefault();
    const psw1 = document.getElementById("id_psw1").value;
    const psw2 = document.getElementById("id_psw2").value;
  
    const display1 = document.getElementById("password-display1");
    const display2 = document.getElementById("password-display2");
  
    // Actualiza el contenido sin modificar la visibilidad
    display1.textContent = psw1;
    display2.textContent = psw2;
  }
  
  function showPasswordLogUp(e) {
    e.preventDefault();
    const display1 = document.getElementById("password-display1");
    const display2 = document.getElementById("password-display2");
    if (this.checked) {
      display1.classList.add("show");
      display2.classList.add("show");
    } else {
      display1.classList.remove("show");
      display2.classList.remove("show");
    }
  }
  
  /* CONTRASEÑA OLVIDADA */
  // Función para mostrar el formulario de contraseña olvidada
  function mostrarContraOlvidada(e) {
    e.preventDefault();
    let login = $("#login");
    let forgot = $("#forgot");
  
    // Aplicamos la animación con easing y forwards para mantener el estado final
    login.css("animation", "flip 1s ease-in-out forwards");
    forgot.css("animation", "backflip 1s ease-in-out forwards");
    forgot.css("display", "block");
  
    setTimeout(() => {
      // Ajustamos los z-index para que se vean en el orden correcto
      forgot.css("z-index", "1");
      login.css("z-index", "-1");
    }, 500);
    showGoBack();
  }
  
  function showGoBack() {
    let loginLink = document.querySelector('a[href="#login"]');
    let signupLink = document.querySelector('a[href="#signup"]');
  
    if (loginLink) {
      //loginLink.removeAttribute('href'); // Quita el href
      loginLink.id = "goBack"; // Cambia el ID
      loginLink.textContent = "Volver atrás"; // Cambia el texto
    }
  
    if (signupLink) {
      signupLink.style.transform = "scale(0)"; // Oculta el botón de "Registrarse"
    }
  }
  
  function restoreLinks() {
    let goBackLink = document.getElementById("goBack");
    let signupLink = document.querySelector('a[href="#signup"]');
  
    if (goBackLink) {
      goBackLink.setAttribute("href", "#login"); // Restaura el href
      goBackLink.id = ""; // Remueve el ID personalizado
      goBackLink.textContent = "Iniciar sesión"; // Restaura el texto original
    }
  
    if (signupLink) {
      signupLink.style.transform = "scale(1)"; // Vuelve a mostrar el botón de "Registrarse"
    }
  
    let login = $("#login");
    let forgot = $("#forgot");
  
    // Aplicamos la animación con easing y forwards para mantener el estado final
    forgot.css("animation", "flip 1s ease-in-out forwards");
    login.css("animation", "backflip 1s ease-in-out forwards");
    login.css("display", "block");
    setTimeout(() => {
      // Ajustamos los z-index para que se vean en el orden correcto
      login.css("z-index", "1");
      forgot.css("z-index", "-1");
    }, 500);
  
    setTimeout(() => {
      login.css("animation", "");
    }, 1000);
  }
  
  function clearForm(e) {
    // Limpiar todos los inputs y select del formulario
    $("form input").val(""); // Limpiar todos los campos input
    $("#toggle-password-logup").prop("checked", false); // Desmarcar el checkbox
    $("#toggle-password").prop("checked", false); // Desmarcar el checkbox
    $(".password-display").empty(); //Vaciar divs que muestran la psw
    $(".error").empty(); // Elimina los mensajes de error generados automáticamente
  
    showPasswordLogIn(e);
    showPasswordLogUp(e);
  
    $(".tab").removeClass("active"); // Quita la clase "active" de todos los tabs
    $('.tab a[href="#login"]').parent().addClass("active"); // Activa el tab de login
  
    $(".tab-content > div").hide(); // Oculta todos los contenidos
    $("#login").fadeIn(600); // Muestra el formulario de login con animación
  
  }