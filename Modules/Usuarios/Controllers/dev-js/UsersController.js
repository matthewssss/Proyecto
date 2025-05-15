jQuery(function () {
  loadUsersData(true);
});

function loadUsersData(cargarMas) {
  $.ajax({
    url: '../Controllers/Servlet.php?action=getUsers',
    type: 'GET',
    dataType: 'html',
    success: function (data) {
      let jsonData;
      try {
        jsonData = JSON.parse(data);
      } catch (e) {
        jsonData = null;
      }
      if (jsonData && jsonData.sesion) {
        window.location.href = '/403';
        return;
      }

      const $tabla = $('#tablaUsuarios');
      if ($.fn.DataTable.isDataTable($tabla)) {
        $tabla.DataTable().destroy(); // Solo si ya está inicializada
      }

      $('#userTableBody').html(data); // Insertar las filas
      loadTable();
      if (cargarMas) {
        loadCosas();
      }
    },
    error: function (xhr, status, error) {
      console.error('Error al cargar los datos de los usuarios:', error);
      $('#userTableBody').html('<tr><td colspan="7" class="text-center">Error al cargar los usuarios</td></tr>');
    }
  });
}

function loadTable() {
  $('#tablaUsuarios').DataTable({
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
      zeroRecords: "No se encontraron resultados.",
      emptyTable: "No hay usuarios disponibles."
    },
    responsive: true,
    orderCellsTop: true,
    initComplete: function () {
      let api = this.api();

      const rolMap = {
        "1": "Padre",
        "2": "Monitor",
        "3": "Secretario Unidad",
        "4": "Coordinador Unidad",
        "5": "Admin"
      };

      $('#filter-rol').on('change', function () {
        const value = this.value;
        const textToSearch = rolMap[value] || '';
        api.column(0).search(textToSearch).draw();
      });

      $('#filter-rama').on('change', function () {
        api.column(1).search(this.value).draw();
      });
    },
    columnDefs: [
      {
        targets: 0, // Columna Rol
        render: function (data, type, row, meta) {
          if (type === 'filter' || type === 'sort') {
            return $('<div>').html(data).find('.display-rol').text(); // Solo para buscar y ordenar
          }
          return data; // Mantén el HTML para la vista (display) y edición
        }
      },
      {
        targets: 1, // Columna Rama
        render: function (data, type, row, meta) {
          if (type === 'filter' || type === 'sort') {
            return $('<div>').html(data).find('.display-rama').text();
          }
          return data;
        }
      }
    ]
  });

}

function loadCosas() {

  // Mostrar el select al hacer clic en el span del rol
  $('#tablaUsuarios').on('click', '.display-rol', function () {
    const $span = $(this);
    const $select = $span.siblings('.edit-rol');

    $span.addClass('d-none');
    $select.removeClass('d-none').focus();
    scheduleChangeCheck();
  });

  // Cuando se cambia el select de rol
  $('#tablaUsuarios').on('change', '.edit-rol', function () {
    const $select = $(this);
    const selectedText = $select.find('option:selected').text();
    const $span = $select.siblings('.display-rol');

    $span.text(selectedText).removeClass('d-none');
    $select.addClass('d-none');

    // Gestión dinámica del campo rama
    const row = $select.closest('tr');
    const ramaSelect = row.find('.edit-rama');
    const ramaDisplay = row.find('.display-rama');

    // Verificar si el rol es "Padre"
    if (selectedText !== 'Padre') {
      ramaSelect.prop('disabled', false).removeClass('d-none');
      ramaDisplay.addClass('d-none');
    } else {
      ramaSelect.val('0'); // ← fuerza el value a 0 ("Seleccione rama")
      ramaSelect.prop('disabled', true).addClass('d-none');
      ramaDisplay.removeClass('d-none').text('');
    }

    if (selectedText === 'Padre') {
      ramaSelect.removeClass('is-invalid');
      row.removeClass('fila-error');
    }

    scheduleChangeCheck();
  });

  // Si el select de rol pierde el foco y no cambió, restauramos el span
  $('#tablaUsuarios').on('focusout', '.edit-rol', function () {
    const $select = $(this);
    setTimeout(() => { // Esperamos un poquito por si el usuario clicó en una opción
      if (!$select.is(':focus')) {
        const $span = $select.siblings('.display-rol');
        const selectedText = $select.find('option:selected').text();

        $span.text(selectedText).removeClass('d-none');
        $select.addClass('d-none');
        scheduleChangeCheck();
      }
    }, 150);
  });

  // Mostrar rama editable
  $('#tablaUsuarios').on('click', '.display-rama', function () {
    const $span = $(this);
    const $select = $span.siblings('.edit-rama');

    if ($select.is(':disabled')) return;

    $span.addClass('d-none');
    $select.removeClass('d-none').focus();
    scheduleChangeCheck();
  });

  // Cambio en rama
  $('#tablaUsuarios').on('change', '.edit-rama', function () {
    const $select = $(this);
    const selectedText = $select.find('option:selected').text();
    const $span = $select.siblings('.display-rama');
    const $row = $select.closest('tr');

    $span.text(selectedText).removeClass('d-none');
    $select.addClass('d-none');

    // Si la selección es válida, quitamos el error
    if ($select.val() !== '0' && $select.val()) {
      $select.removeClass('is-invalid');
      $row.removeClass('fila-error');
    }

    scheduleChangeCheck();
  });

  // Si el select de rama pierde el foco
  $('#tablaUsuarios').on('focusout', '.edit-rama', function () {
    const $select = $(this);
    setTimeout(() => {
      if (!$select.is(':focus')) {
        const $span = $select.siblings('.display-rama');
        const selectedText = $select.find('option:selected').text();

        $span.text(selectedText).removeClass('d-none');
        $select.addClass('d-none');
        scheduleChangeCheck();
      }
    }, 150);
  });

  function scheduleChangeCheck() {
    setTimeout(checkForChanges, 50);
  }

  function checkForChanges() {
    let cambios = false;

    $('#tablaUsuarios tbody tr').each(function () {
      const $row = $(this);
      const $rol = $row.find('.edit-rol');
      const $rama = $row.find('.edit-rama');

      const rolOriginal = parseInt($rol.data('original'));
      const ramaOriginal = $rama.data('original');

      const rolActual = parseInt($rol.val());
      const ramaActual = $rama.val();

      let rowModificado = false;

      if (rolActual !== rolOriginal) {
        rowModificado = true;
      }

      if (!$rama.prop('disabled') && ramaActual !== String(ramaOriginal)) {
        rowModificado = true;
      }

      if (rowModificado) {
        cambios = true;
        $row.addClass('modificado');
      } else {
        $row.removeClass('modificado');
      }
    });

    toggleGuardarBtn();
  }

}

function toggleGuardarBtn() {
  const filasModificadas = $('#tablaUsuarios tbody tr.modificado');
  const hayCambios = filasModificadas.length > 0;

  $('#guardarCambios').prop('disabled', !hayCambios);
  $('#filtrarModificados').prop('disabled', !hayCambios);
}

let mostrandoSoloModificados = false;

$('#filtrarModificados').on('click', function () {
  if (!mostrandoSoloModificados) {
    $('#tablaUsuarios tbody tr').not('.modificado').hide();
    $(this).html('<i class="fas fa-filter"></i> Ver Todo');
  } else {
    $('#tablaUsuarios tbody tr').show();
    $(this).html('<i class="fas fa-filter"></i> Ver cambios');
  }

  mostrandoSoloModificados = !mostrandoSoloModificados;
});

$('#guardarCambios').on('click', function () {

  const datos = [];
  let error = false;

  $('#tablaUsuarios tbody tr.modificado').each(function () {
    const $row = $(this);
    const id = $row.data('id');
    const rol = parseInt($row.find('.edit-rol').val());
    const $ramaSelect = $row.find('.edit-rama');
    const rama = $ramaSelect.val();

    // Limpiar clases previas
    $row.removeClass('fila-error');
    $ramaSelect.removeClass('is-invalid');

    // Validación: Si no es Padre, rama debe estar seleccionada
    if (rol !== 1 && (rama === '0' || !rama)) {
      $ramaSelect.addClass('is-invalid');
      $row.addClass('fila-error');
      $ramaSelect.trigger('focus');
      error = true;
      return false; // corta el each
    }

    datos.push({ id, rol, rama });
  });

  if (error || datos.length === 0) return;

  Swal.fire({
    title: '¿Quieres guardar los datos?',
    html: 'Los usuarios que <strong>no tengan rol de Padre</strong> y hayan sido modificados serán notificados por <strong>correo electrónico</strong> del cambio realizado.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Guardar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      saveData();
    }
  });

  function saveData() {
    $.ajax({
      url: '../Controllers/Servlet.php',
      method: 'POST',
      data: {
        action: 'updateUsersRol',
        cambios: JSON.stringify(datos)
      },
      success: function (response) {
        if (response.error) {
          let listaErrores = '<ul style="text-align: left;">' + response.msg + '</ul>';

          Swal.fire({
            icon: 'error',
            title: 'Error al notificar',
            html: `
                    <p>Hubo errores al enviar las notificaciones por correo electrónico:</p>
                    ${listaErrores}
                `,
            confirmButtonText: 'OK',
            customClass: {
              popup: 'swal-wide'
            }
          });
        } else {
          $('#tablaUsuarios tbody tr.modificado')
            .removeClass('modificado fila-error')
            .css('transition', 'background-color 0.4s')
            .css('background-color', '#d4edda');

          toggleGuardarBtn();
          loadUsersData(false);

          setTimeout(() => {
            $('#tablaUsuarios tbody tr').css('background-color', '');
          }, 1000);

          Swal.fire({
            toast: true,
            position: 'top',
            icon: 'success',
            title: 'Cambios de rol guardados correctamente',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
          });
        }
      },
      error: function (err) {
        console.error('Error guardando:', err);
        Swal.fire({
          toast: true,
          position: 'top',
          icon: 'error',
          title: 'Error al guardar los cambios de rol',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
      }

    });
  }
});

$(document).on('click', '.edit-user', function () {
  const userData = {
    id: $(this).data('id'),
    nombre: $(this).data('nombre'),
    apellidos: $(this).data('apellidos'),
    correo: $(this).data('correo'),
    tel: $(this).data('tel'),
    rol: $(this).data('rol'),
    rama: $(this).data('rama'),
  };

  openEditUserSwal(userData);
});


let userDataEdited = null;

function openEditUserSwal(userData) {
  Swal.fire({
    title: 'Editar Usuario',
    html: `
        <form id="swalEditForm" class="text-start">
          <input type="hidden" id="userId" value="${userData.id}">
  
          <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="${userData.nombre}">
          </div>
  
          <div class="mb-3">
            <label for="apellidos" class="form-label">Apellidos</label>
            <input type="text" class="form-control" id="apellidos" name="apellidos" value="${userData.apellidos}">
          </div>
  
          <div class="mb-3" id="divCorreo">
            <label for="correo" class="form-label">Correo</label>
            <input type="email" class="form-control" id="correo" name="correo" value="${userData.correo}" readonly>
          </div>
  
          <div class="mb-3" id="divTelefono">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" value="${userData.tel || userData.telefono}" readonly>
          </div>
  
          <div class="mb-3">
            <label for="password" class="form-label">Contraseña (opcional)</label>
            <div class="input-group">
              <input type="password" class="form-control" id="password" name="password" value="${userData.password ? userData.password : ''}" placeholder="Nueva contraseña">
              <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                👁️
              </button>
            </div>
          </div>
  
          <div class="mb-3">
            <label for="rol" class="form-label">Rol</label>
            <select class="form-select" id="rol" name="rol">
              <option value="1" ${userData.rol == 1 ? 'selected' : ''}>Padre</option>
              <option value="2" ${userData.rol == 2 ? 'selected' : ''}>Monitor</option>
              <option value="3" ${userData.rol == 3 ? 'selected' : ''}>Secretario Unidad</option>
              <option value="4" ${userData.rol == 4 ? 'selected' : ''}>Coordinador Unidad</option>
              <option value="5" ${userData.rol == 5 ? 'selected' : ''}>Admin</option>
            </select>
          </div>
  
          <div class="mb-3">
            <label for="rama" class="form-label">Rama</label>
            <select class="form-select" id="rama" name="rama">
              <option value="">Seleccione rama</option>
              <option value="Lobatos" ${userData.rama == 'Lobatos' ? 'selected' : ''}>Lobatos</option>
              <option value="Exploradores" ${userData.rama == 'Exploradores' ? 'selected' : ''}>Exploradores</option>
              <option value="Pioneros" ${userData.rama == 'Pioneros' ? 'selected' : ''}>Pioneros</option>
              <option value="Rutas" ${userData.rama == 'Rutas' ? 'selected' : ''}>Rutas</option>
            </select>
          </div>
        </form>
      `,
    showCancelButton: true,
    confirmButtonText: 'Guardar cambios',
    cancelButtonText: 'Cancelar',
    focusConfirm: false,
    customClass: {
      popup: 'swal-wide no-icon'
    },
    didOpen: () => {
      $('#rol').on('change', updateRamaValidation); //Evento del cambio
      updateRamaValidation(); //Cargar para tenerlo

      $('#telefono, #correo').on('click', function () {
        const $campo = $(this);

        if (!$campo.hasClass('is-invalid')) {
          $campo.removeClass('is-valid').addClass('is-invalid');

          // Mostrar mensaje de error si no existe ya
          if ($campo.next('.invalid-feedback').length === 0) {
            $campo.after('<div class="invalid-feedback">Solo el propio usuario puede modificar este dato.</div>');
          }
        }
      });

      $('#togglePassword').on('click', function () {
        const passwordInput = $('#password');
        const currentType = passwordInput.attr('type');
        passwordInput.attr('type', currentType === 'password' ? 'text' : 'password');
      });

      $.validator.addMethod("requiredIfRolSuperior", function (value, element) {
        const rol = $('#rol').val();
        if (rol !== "1") {
          return value !== "";
        }
        return true; // Si es padre, no requiere rama
      }, "Debe seleccionar una rama para ese rol.");


      $('#swalEditForm').validate({
        ignore: '#correo, #telefono',
        rules: {
          nombre: { required: true },
          apellidos: { required: true },
          rol: { required: true },
          rama: { requiredIfRolSuperior: true }
        },
        messages: {
          nombre: "Introduce un nombre",
          apellidos: "Introduce los apellidos"
        },
        errorClass: "is-invalid",
        validClass: "is-valid",
        errorPlacement: function (error, element) {
          error.insertAfter(element);
        }
      });
    },
    preConfirm: () => {
      if (!$('#swalEditForm').valid()) {
        Swal.showValidationMessage('Por favor, corrige los errores antes de continuar');
        return false;
      }

      const formData = {
        id: $('#userId').val(),
        nombre: $('#nombre').val(),
        apellidos: $('#apellidos').val(),
        correo: $('#correo').val(),
        telefono: $('#telefono').val(),
        password: $('#password').val(),
        rol: $('#rol').val(),
        rama: $('#rama').val()
      };

      // Guardamos temporalmente para uso posterior
      userDataEdited = formData;

      return formData;
    }
  }).then((result) => {
    if (result.isConfirmed && result.value) {
      Swal.fire({
        title: '¿Quieres guardar los datos?',
        html: 'Los usuarios que <strong>no tengan rol de Padre</strong> y hayan sido modificados serán notificados por <strong>correo electrónico</strong> del cambio realizado.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          saveTotalData(userDataEdited);
        } else {
          openEditUserSwal(userDataEdited);
        }
      });

    }
  });
}

function updateRamaValidation() {
  const rol = $('#rol').val();

  if (rol === "1") {
    $('#rama').val("").prop('disabled', true).removeClass('is-invalid is-valid');
    $('label[for="rama"]').text("Rama (no aplica para Padres)");
  } else {
    $('#rama').prop('disabled', false);
    $('label[for="rama"]').text("Rama");

    // Si rama está vacío y es requerido, fuerza validación
    if ($('#rama').val() === "") {
      $('#rama').addClass('is-invalid');
    }
  }
}

function saveTotalData(formData) {
  const passwordChanged = formData.password.trim() !== '';

  // Mostrar un modal de "Guardando..." más amigable mientras carga
  Swal.fire({
    title: 'Guardando cambios...',
    text: 'Por favor, espera un momento.',
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  $.ajax({
    url: '../Controllers/Servlet.php',
    type: 'POST',
    dataType: 'json',
    data: {
      action: 'updateData',
      profile: false,
      ...formData
    },
    success: function (res) {
      if (res.success) {
        Swal.fire({
          toast: true,
          icon: 'success',
          position: 'top',
          title: passwordChanged
            ? 'Usuario actualizado y contraseña enviada'
            : 'Usuario actualizado con éxito',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
        });
        loadUsersData(false);
      } else {
        Swal.fire('Error', res.message || 'No se pudo actualizar el usuario', 'error');
      }
    },
    error: function (err) {
      console.error(err);
      Swal.fire('Error', 'Error de servidor', 'error');
    }
  });
}


$(document).on('click', '.delete-user', function () {
  const userId = $(this).data('id');

  Swal.fire({
    title: "¿Estás seguro?",
    text: "El usuario será eliminadoger4t4.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: '../Controllers/Servlet.php',
        type: 'POST',
        data: {
          action: 'deleteUser',
          id: userId
        },
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            Swal.fire('Desactivado', response.message, 'success');
            loadUsersData(false);
          } else {
            Swal.fire('Error', response.message, 'error');
          }
        },
        error: function () {
          Swal.fire('Error', 'No se pudo completar la solicitud.', 'error');
        }
      });
    }
  });
});