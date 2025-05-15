function openCircularModal(tipo = 'crear', datos = {}) {
    const isEditar = tipo === 'editar';
    const isClonar = tipo === 'clonar';

    Swal.fire({
        title: isEditar ? "Editar circular" : isClonar ? "Clonar circular" : "Crear nueva circular",
        width: 600,
        html: `
            <form id="formNuevaCircular" class="createModal">
                <div class="mb-2">
                    <input type="hidden" id="id" value="${datos.id || ''}">
                </div>
                <div class="mb-2">
                    <label for="titulo">Título:</label>
                    <input type="text" class="form-control" id="titulo" required>
                    <div class="invalid-feedback">El título es obligatorio.</div>
                </div>

                <div class="mb-2">
                    <label for="fechaInicio">Fecha de inicio:</label>
                    <input type="date" class="form-control" id="fechaInicio" required>
                    <div class="invalid-feedback">Fecha de inicio requerida.</div>
                </div>

                <div class="mb-2">
                    <label for="fechaFin">Fecha de fin:</label>
                    <input type="date" class="form-control" id="fechaFin" required>
                    <div class="invalid-feedback" id="errorFechaNoValida">Fecha de fin requerida.</div>
                </div>

                <div class="mb-2" id="pernoctaContainer" style="display: none;">
                    <label for="pernoctas">¿Cuántas pernoctas?</label>
                    <input type="number" class="form-control" id="pernoctas" min="0" readonly>
                    <div class="invalid-feedback">Indica número de pernoctas.</div>
                </div>

                <div class="mb-2">
                    <label for="ubicacion">Ubicación:</label>
                    <input type="text" class="form-control" id="ubicacion" required>
                    <div class="invalid-feedback">La ubicación es obligatoria.</div>
                </div>

                <div class="mb-2">
                    <label for="rama">Rama:</label>
                    <select class="form-control" id="rama" required>
                        <option value="">Selecciona una rama</option>
                        <option value="Lobatos">Lobatos</option>
                        <option value="Exploradores">Exploradores</option>
                        <option value="Pioneros">Pioneros</option>
                        <option value="General">General</option>
                    </select>
                    <div class="invalid-feedback">La rama es obligatoria.</div>
                </div>

                <div class="mb-3">
                    <label for="archivo">Archivo PDF:</label>
                    <div class="fileUploader">
                        <div class="header">
                            <i class="fa fa-file-upload"></i>
                            <p>Sube el archivo PDF</p>
                        </div>
                        <div class="footer">
                            <i class="fa fa-file-upload" id="archivoIcono"></i>
                            <p id="archivoNombre">Ningún archivo seleccionado</p>
                            <i class="fa fa-trash" id="archivoEliminar" style="display: none;"></i>
                        </div>
                        <input id="inputFileId" type="file" accept="application/pdf" style="display: none;">
                    </div>
                    <div class="invalid-feedback" id="errorFileInput">Selecciona un archivo PDF.</div>
                </div>
            </form>
        `,
        confirmButtonText: isEditar ? "Guardar cambios" : isClonar ? "Clonar circular" : "Subir circular",
        showCancelButton: true,
        didOpen: () => {
            loadDragDrop();
            loadFechasRules();
            if (isEditar || isClonar) {
                document.getElementById("titulo").value = datos.titulo || '';
                document.getElementById("fechaInicio").value = datos.fechaInicio || '';
                document.getElementById("fechaFin").value = datos.fechaFin || '';
                document.getElementById("pernoctas").value = datos.pernoctas || 0;
                document.getElementById("ubicacion").value = datos.ubicacion || '';
                document.getElementById("rama").value = datos.rama || '';
            }

            if (isEditar && datos.path) {
                const archivoIcono = document.getElementById('archivoIcono');
                const archivoNombre = document.getElementById('archivoNombre');
                const archivoEliminar = document.getElementById('archivoEliminar');
                const archivoInput = document.getElementById('inputFileId');

                archivoIcono.classList.remove('fa-file-upload');
                archivoIcono.classList.add('fa-file-pdf');
                archivoNombre.textContent = datos.path.split('/').pop();  // Nombre del archivo
                archivoEliminar.style.display = 'inline';

                // Marcar como "válido" aunque el input esté vacío
                archivoInput.classList.add("is-valid");

                // Opción: guardar una flag tipo "archivoYaCargado" si quieres manejarlo luego
                archivoInput.dataset.existing = "true";
            }

        },
        preConfirm: () => {
            let isValid = true;

            // Validar campos obligatorios
            const requiredFields = ["titulo", "fechaInicio", "fechaFin", "rama", "ubicacion"];
            requiredFields.forEach(id => {
                const el = document.getElementById(id);
                if (!el.value.trim()) {
                    el.classList.add("is-invalid");
                    isValid = false;
                } else {
                    el.classList.remove("is-invalid");
                }
            });

            // Validación de fechas
            const fechaInicio = document.getElementById("fechaInicio");
            const fechaFin = document.getElementById("fechaFin");
            const inicioDate = new Date(fechaInicio.value);
            const finDate = new Date(fechaFin.value);
            if (finDate < inicioDate) {
                isValid = false;
                fechaFin.classList.add("is-invalid");
                $("#errorFechaNoValida").text("La fecha de fin no puede ser menor que la de inicio");
            } else {
                fechaFin.classList.remove("is-invalid");
            }

            // Validar archivo (nuevo o ya cargado)
            const archivoInput = document.getElementById("inputFileId");
            const archivoNuevo = archivoInput.files.length > 0;
            const archivoExistente = archivoInput.dataset.existing === "true";

            const MAX_FILE_SIZE_MB = 20;
            const MAX_FILE_SIZE_BYTES = MAX_FILE_SIZE_MB * 1024 * 1024;

            if (!archivoNuevo && !archivoExistente) {
                archivoInput.classList.add("is-invalid");
                $("#errorFileInput").text("Debes seleccionar un archivo.").show();
                isValid = false;
            } else if (archivoNuevo) {
                const archivo = archivoInput.files[0];
                if (archivo.size > MAX_FILE_SIZE_BYTES) {
                    archivoInput.classList.add("is-invalid");
                    $("#errorFileInput").text(`El archivo excede el tamaño máximo de ${MAX_FILE_SIZE_MB} MB.`).show();
                    isValid = false;
                } else {
                    archivoInput.classList.remove("is-invalid");
                    $("#errorFileInput").hide();
                }
            } else {
                archivoInput.classList.remove("is-invalid");
                $("#errorFileInput").hide();
            }

            if (!isValid) {
                Swal.showValidationMessage("Por favor, corrige los errores del formulario.");
                return false;
            }


            // Si es válido, preparamos la data
            const data = new FormData();
            data.append('titulo', document.getElementById("titulo").value.trim());
            data.append('fecha_inicio', document.getElementById("fechaInicio").value);
            data.append('fecha_fin', document.getElementById("fechaFin").value);
            data.append('pernoctas', document.getElementById("pernoctas").value || 0);
            data.append('ubicacion', document.getElementById("ubicacion").value);
            data.append('rama', document.getElementById("rama").value);

            if (isEditar) {
                data.append('id_file', document.getElementById("id").value);
            }

            // Añadir archivo si es nuevo
            if (archivoNuevo) {
                data.append('archivo', archivoInput.files[0]);
            }

            // Marcar si es un archivo ya existente (por si necesitas manejarlo en el backend)
            if (archivoExistente) {
                data.append('archivoExistente', 'true');
            }

            Swal.showLoading();
            // Retornar promesa
            return $.ajax({
                url: '../Controllers/Servlet.php?action=' + (isEditar ? 'editCircular' : 'createCircular'),
                type: 'POST',
                data: data,
                contentType: false,
                processData: false,
                dataType: 'json'
            });
        }
    }).then((response) => {
        if (!response) {
            Swal.fire('Error', 'No se recibió respuesta del servidor.', 'error');
            return;
        }
    
        if (response.value.error) {
            // Mostrar el mensaje de error y volver a abrir el modal con los datos anteriores
            Swal.fire({
                title: 'Error',
                text: response.value.message || 'Ocurrió un error.',
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Volver a intentar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    icon: 'mi-popup-central'
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    //Reabrimos el modal con los mismos datos
                    let modalTipo = isEditar ? 'editar' : isClonar ? 'clonar' : 'crear';
                    openCircularModal(
                        modalTipo,
                        response.value.reintentoDatos || {}
                    );
                }
            });
        } else {
            // Éxito
            Swal.fire({
                title: '¡Circular guardada!',
                text: response.value.message || 'La circular se ha guardado correctamente.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                customClass: {
                    icon: 'mi-popup-central'
                },
            }).then(() => {
                Swal.close();
                location.reload();  
            });
        }
    });
    

    function loadDragDrop() {
        const archivoInput = document.getElementById('inputFileId');
        const archivoIcono = document.getElementById('archivoIcono');
        const archivoNombre = document.getElementById('archivoNombre');
        const archivoEliminar = document.getElementById('archivoEliminar');
        const fileUploader = document.querySelector('.fileUploader');

        // Permite abrir el explorador de archivos al hacer clic en el uploader
        fileUploader.addEventListener('click', (e) => {
            if (!e.target.closest('#archivoEliminar')) {
                archivoInput.click();
            }
        });


        // Refresca visualmente el uploader según el archivo
        function actualizarVistaArchivo(file) {
            archivoIcono.classList.remove('fa-file-upload', 'fa-arrow-circle-right');
            archivoIcono.classList.add('fa-file-pdf');
            archivoNombre.textContent = file.name;
            archivoEliminar.style.display = 'inline';
            archivoInput.classList.add("is-valid");
        }

        function resetUploader() {
            archivoInput.value = "";
            archivoInput.removeAttribute("data-existing");
            archivoIcono.classList.remove('fa-file-pdf', 'fa-arrow-circle-right');
            archivoIcono.classList.add('fa-file-upload');
            archivoNombre.textContent = "Ningún archivo seleccionado";
            archivoEliminar.style.display = "none";
            archivoInput.classList.remove("is-valid");
            fileUploader.classList.remove("drag-over");
        }

        archivoInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                actualizarVistaArchivo(e.target.files[0]);
            } else {
                resetUploader();
            }
        });

        archivoEliminar.addEventListener('click', (e) => {
            e.stopPropagation(); // Evita que se active el input file al hacer clic
            resetUploader();
        });

        fileUploader.addEventListener('dragover', (e) => {
            e.preventDefault();
            archivoIcono.classList.remove('fa-file-upload');
            archivoIcono.classList.add('fa-arrow-circle-right');
            archivoNombre.textContent = "¡Suelta el archivo aquí!";
            fileUploader.classList.add('drag-over');
        });

        fileUploader.addEventListener('dragleave', () => {
            archivoIcono.classList.remove('fa-arrow-circle-right');
            archivoIcono.classList.add('fa-file-upload');
            archivoNombre.textContent = archivoInput.files.length
                ? archivoInput.files[0].name
                : "Ningún archivo seleccionado";
            fileUploader.classList.remove('drag-over');
        });

        fileUploader.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploader.classList.remove('drag-over');
            archivoIcono.classList.remove('fa-arrow-circle-right');

            if (e.dataTransfer.files.length > 0) {
                archivoInput.files = e.dataTransfer.files;
                actualizarVistaArchivo(e.dataTransfer.files[0]);
            } else {
                archivoIcono.classList.add('fa-file-upload');
                archivoNombre.textContent = "Ningún archivo seleccionado";
            }
        });

        // Refrescar vista si ya hay un archivo cargado (por ejemplo al editar)
        if (archivoInput.files.length > 0) {
            const file = archivoInput.files[0];
            archivoIcono.classList.remove('fa-file-upload');
            archivoIcono.classList.add('fa-file-pdf');
            archivoNombre.textContent = file.name;
            archivoEliminar.style.display = 'inline';
            archivoInput.classList.add('is-valid');
        }

    }


    function loadFechasRules() {
        const fechaInicio = document.getElementById('fechaInicio');
        const fechaFin = document.getElementById('fechaFin');
        const pernoctaContainer = document.getElementById('pernoctaContainer');
        const pernoctasInput = document.getElementById('pernoctas');

        // Establecer las fechas mínimas en el input
        const today = new Date().toISOString().split('T')[0]; // Obtiene la fecha de hoy en formato 'YYYY-MM-DD'
        fechaInicio.setAttribute('min', today);
        fechaFin.setAttribute('min', today);

        // Validar que la fecha de fin no sea menor que la de inicio
        function togglePernocta() {
            const inicio = new Date(fechaInicio.value);
            const fin = new Date(fechaFin.value);

            // Resetear las clases a su estado original
            fechaInicio.classList.remove("is-invalid", "is-valid");
            fechaFin.classList.remove("is-invalid", "is-valid");

            // Validación de fecha de inicio
            if (!fechaInicio.value) {
                fechaInicio.classList.add("is-invalid");
                $("#errorFechaNoValida").text("Fecha de fin requerida");
            } else {
                fechaInicio.classList.add("is-valid");
            }

            // Validación de fecha de fin
            if (!fechaFin.value) {
                fechaFin.classList.add("is-invalid");
                $("#errorFechaNoValida").text("Fecha de fin requerida");
            } else if (fin < inicio) {
                fechaFin.classList.add("is-invalid");
                $("#errorFechaNoValida").text("La fecha de fin no puede ser menor que la de inicio");
            } else {
                fechaFin.classList.add("is-valid");
            }

            // Si las fechas son válidas, calculamos las pernoctas y mostramos el contenedor
            if (fin > inicio) {
                pernoctaContainer.style.display = 'block';
                const pernoctas = Math.ceil((fin - inicio) / (1000 * 3600 * 24)); // Calcula los días de diferencia
                pernoctasInput.value = pernoctas;
            } else {
                pernoctaContainer.style.display = 'none';
            }
        }

        // Escuchar los cambios en las fechas
        fechaInicio.addEventListener('change', togglePernocta);
        fechaFin.addEventListener('change', togglePernocta);
    }
}