jQuery(function () {
    loadLogInForm();
    loadSignInForm();
    loadLostPasswordForm();
    loadEditProfile();
  })
  
  function loadLogInForm() {
    $("#loginForm").validate({
      rules: {
        email: {
          required: true,
          email: true
        },
        password: {
          required: true,
          minlength: 6
        }
      },
      messages: {
        email: {
          required: "Inserte un correo electronico",
          email: "Inserte un correo electronico valido"
        },
        password: {
          required: "Inserte una contraseña",
          minlength: "La contraseña debe tener al menos 6 caracteres"
        }
      },
      errorPlacement: function (error, element) {
        if (element.attr("name") === "password") {
          error.appendTo("#error-password"); // Aparece en un div específico
        } else {
          error.insertBefore(element); // El resto se muestra normalmente
        }
      }
    });
  }
  
  function loadSignInForm() {
    $("#signin").validate({
      rules: {
        nombre: {
          required: true,
          minlength: 2,
        },
        apellido: {
          required: true,
          minlength: 2,
        },
        email: {
          required: true,
          email: true
        },
        telefono: {
          required: true,
          digits: true,
          minlength: 9,
          maxlength: 15
        },
        password1: {
          required: true,
          minlength: 6
        },
        password2: {
          required: true,
          equalTo: "#id_psw1"
        }
      },
      messages: {
        nombre: {
          required: "Rellene este campo",
          minlength: "Debe tener al menos 2 caracteres.",
        },
        apellido: {
          required: "Rellene este campo",
          minlength: "Debe tener al menos 2 caracteres.",
        },
        email: {
          required: "Inserte un correo electronico",
          email: "Inserte un correo electronico valido"
        },
        telefono: {
          required: "Por favor, ingresa tu número de teléfono.",
          digits: "Solo se permiten números.",
          minlength: "El teléfono debe tener al menos 9 dígitos.",
          maxlength: "El teléfono no puede tener más de 15 dígitos."
        },
        password1: {
          required: "Inserte una contraseña",
          minlength: "La contraseña debe tener al menos 6 caracteres"
        },
        password2: {
          required: "Inserte una contraseña",
          equalTo: "Las contraseñas no son iguales"
        }
      },
      errorPlacement: function (error, element) {
        if (element.attr("name") === "password1") {
          error.appendTo("#passwordErr"); // Aparece en un div específico
        } else if (element.attr("name") === "password2") {
          error.appendTo("#passwordErr2"); // Aparece en un div específico
        } else {
          error.insertAfter(element); // El resto se muestra normalmente
        }
      }
    });
  }
  
  function loadLostPasswordForm() {
    $("#resetPassword").validate({
      rules: {
        email: {
          required: true,
          email: true
        }
      },
      messages: {
        email: {
          required: "Inserte un correo electronico",
          email: "Inserte un correo electronico valido"
        }
      },
      errorPlacement: function (error, element) {
        error.insertBefore(element); // El resto se muestra normalmente
      }
    });
  }
  
  function loadEditProfile() {
    $("#editForm").validate({
      rules: {
        nombre: {
          required: true
        },
        apellidos: {
          required: true
        },
        correo: {
          required: true,
          email: true // Validar formato de correo
        },
        telefono: {
          required: true,
          digits: true,  // Asegura que solo números sean ingresados
          minlength: 9,  // Asegura que el teléfono tenga al menos 10 dígitos
          maxlength: 15   // Limita el número máximo de dígitos
        },
        password: {
          minlength: 6 // Si se proporciona una contraseña, debe tener al menos 6 caracteres
        }
      },
      messages: {
        nombre: {
          required: "Por favor, ingresa tu nombre."
        },
        apellidos: {
          required: "Por favor, ingresa tus apellidos."
        },
        correo: {
          required: "Por favor, ingresa un correo electrónico.",
          email: "Por favor, ingresa un correo electrónico válido."
        },
        telefono: {
          required: "Por favor, ingresa tu teléfono.",
          digits: "Por favor, ingresa solo números.",
          minlength: "El número de teléfono debe tener al menos 10 dígitos.",
          maxlength: "El número de teléfono no puede tener más de 15 dígitos."
        },
        password: {
          minlength: "La contraseña debe tener al menos 6 caracteres."
        }
      }
    });
  }