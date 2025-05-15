$(document).ready(function () {
    // Inicializamos el formulario con la validación de jQuery
    $("#asociadoForm").validate({
      // Definir reglas y mensajes por defecto
      rules: {
        nombre: {
          required: true,
          maxlength: 100
        },
        apellidos: {
          required: true,
          maxlength: 100
        },
        DNI: {
          required: true,
          maxlength: 9
        },
        fecha_nacimiento: {
          required: true
        },
        edad: {
          required: true
        },
        unidad: {
          required: true
        },
        cp: {
          required: true,
          maxlength: 5
        },
        municipio: {
          required: true
        },
        provincia: {
          required: true
        },
        comunidad_autonoma: {
          required: true
        },
        nombre_padre: {
          required: true
        },
        apellidos_padre: {
          required: true
        },
        dni_padre: {
          required: true,
          maxlength: 9
        },
        correo_padre: {
          required: true,
          email: true
        },
        telefono_padre: {
          required: true
        },
        estado_civil: {
          required: true
        }
      },
      messages: {
        nombre: "Por favor, ingresa el nombre.",
        apellidos: "Por favor, ingresa los apellidos.",
        DNI: "Por favor, ingresa el DNI.",
        fecha_nacimiento: "Por favor, selecciona la fecha de nacimiento.",
        edad: "Por favor, ingresa la edad.",
        unidad: "Por favor, ingresa la unidad.",
        cp: "Por favor, ingresa el código postal.",
        municipio: "Por favor, ingresa el municipio.",
        provincia: "Por favor, ingresa la provincia.",
        comunidad_autonoma: "Por favor, ingresa la comunidad autónoma.",
        nombre_padre: "Por favor, ingresa el nombre del padre/tutor.",
        apellidos_padre: "Por favor, ingresa los apellidos del padre/tutor.",
        dni_padre: "Por favor, ingresa el DNI del padre/tutor.",
        correo_padre: "Por favor, ingresa un correo electrónico válido.",
        telefono_padre: "Por favor, ingresa el teléfono del padre/tutor.",
        estado_civil: "Por favor, selecciona el estado civil."
      },
      // Activar validación solo en el paso visible
      errorPlacement: function (error, element) {
        error.insertBefore(element);
      }
    });
  
  });
  