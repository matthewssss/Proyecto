<?php

function createCircular($connect) {
    // Obtener los datos del formulario
    $titulo = $_POST['titulo'] ?? '';
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';
    $pernoctas = $_POST['pernoctas'] ?? 0;
    $ubicacion = $_POST['ubicacion'] ?? '';
    $rama = $_POST['rama'] ?? '';

    $idUsr = $_SESSION['user_id'];

    // Validar los datos antes de insertarlos
    if (empty($titulo) || empty($fecha_inicio) || empty($fecha_fin) || empty($ubicacion) || empty($rama)) {
        echo json_encode(['error' => true, 'message' => 'Todos los campos son obligatorios.', 'reintentoDatos' => $_POST]);
        exit;
    }


    $sqlCheck = "SELECT titulo, fecha_inicio_actividad, fecha_fin_actividad 
            FROM circulares 
            WHERE rama = :rama 
              AND (
                (fecha_inicio_actividad <= :fecha_fin AND fecha_fin_actividad >= :fecha_inicio)
              ) 
            LIMIT 1";

    $stmtCheck = $connect->prepare($sqlCheck);
    $stmtCheck->bindParam(':rama', $rama);
    $stmtCheck->bindParam(':fecha_inicio', $fecha_inicio);
    $stmtCheck->bindParam(':fecha_fin', $fecha_fin);
    $stmtCheck->execute();

    if ($stmtCheck->rowCount() > 0) {
        $existeCircular = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        $mensaje = "Ya existe una circular para la rama " . $rama . " entre el " . 
               date("d/m/Y", strtotime($existeCircular['fecha_inicio_actividad'])) . 
               " y el " . 
               date("d/m/Y", strtotime($existeCircular['fecha_fin_actividad'])) . 
               " (" . $existeCircular['titulo'] . ").";

        echo json_encode([
            'error' => true,
            'message' => $mensaje,
            'reintentoDatos' => $_POST
        ]);
        exit;
    }



    // Procesar el archivo subido
    if ($_FILES['archivo']) {
        $archivo = $_FILES['archivo'];
        $archivoTmp = $archivo['tmp_name'];
        $titulo = $_POST['titulo'];  // Obtenemos el título desde el formulario
        $fechaInicio = $_POST['fecha_inicio'];  // Obtenemos la fecha de inicio desde el formulario

        // Limpiamos el título para evitar problemas con caracteres especiales
        $tituloLimpio = preg_replace("/[^a-zA-Z0-9]+/", "-", $titulo);  // Solo caracteres alfanuméricos y guiones
        $fechaLimpia = str_replace('-', '', $fechaInicio);  // Quitamos los guiones de la fecha

        // Creamos un nombre de archivo único basado en el título y la fecha de inicio
        $archivoNombre = $tituloLimpio . '-' . $fechaLimpia . '.pdf';

        // Ruta interna en el servidor
        $destino = '/var/www/gruposcout.online/public_html/Modules/Circulares/Pdfs/' . $archivoNombre;  // Ruta interna

        // Comprobar si el archivo ya existe
        if (file_exists($destino)) {
            $response = [
                'error' => true,
                'message' => 'El archivo ya existe.',
                'reintentoDatos' => $_POST
            ];
        } else {
            // Mover el archivo a la carpeta de destino
            if (move_uploaded_file($archivoTmp, $destino)) {
                // Ruta pública para acceder al archivo
                $rutaArchivo = "https://gruposcout.online/Modules/Circulares/Pdfs/" . $archivoNombre;  // Ruta pública

                // Preparar la consulta SQL para insertar los datos en la base de datos
                $sql = "INSERT INTO circulares (titulo, archivo_path, fecha_inicio_actividad, fecha_fin_actividad, pernoctas, ubicacion, rama, creado_por) 
                VALUES (:titulo, :pathFile, :fecha_inicio, :fecha_fin, :pernoctas, :ubicacion, :rama, :creado_por)";

                $stmt = $connect->prepare($sql);

                // Bindear cada parámetro por separado
                $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
                $stmt->bindParam(':pathFile', $rutaArchivo, PDO::PARAM_STR);
                $stmt->bindParam(':fecha_inicio', $fecha_inicio, PDO::PARAM_STR);
                $stmt->bindParam(':fecha_fin', $fecha_fin, PDO::PARAM_STR);
                $stmt->bindParam(':pernoctas', $pernoctas, PDO::PARAM_INT);
                $stmt->bindParam(':ubicacion', $ubicacion, PDO::PARAM_STR);
                $stmt->bindParam(':rama', $rama, PDO::PARAM_STR);
                $stmt->bindParam(':creado_por', $idUsr, PDO::PARAM_STR);

                // Ejecutar la consulta y verificar si fue exitosa
                if ($stmt->execute()) {
                    $response = [
                        'error' => false,
                        'message' => 'La circular fue creada con éxito',
                    ];
                } else {
                    $response = [
                        'error' => true,
                        'message' => 'Hubo un error al guardar la circular en la base de datos.',
                        'reintentoDatos' => $_POST
                    ];
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => 'Error al mover el archivo.',
                    'reintentoDatos' => $_POST
                ];
            }
        }
    } else {
        $response = [
            'error' => true,
            'message' => 'No se recibió un archivo válido.',
            'reintentoDatos' => $_POST
        ];
    }


    echo json_encode($response);
}