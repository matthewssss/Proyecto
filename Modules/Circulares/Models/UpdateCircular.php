<?php

function updateCircular($connect) {
    $id_file = $_POST['id_file'] ?? null;
    $titulo = $_POST['titulo'] ?? '';
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';
    $pernoctas = $_POST['pernoctas'] ?? 0;
    $ubicacion = $_POST['ubicacion'] ?? '';
    $rama = $_POST['rama'] ?? '';

    $idUsr = $_SESSION['user_id'];

    if (empty($titulo) || empty($fecha_inicio) || empty($fecha_fin) || empty($ubicacion) || empty($rama)) {
        echo json_encode(['error' => true, 'message' => 'Todos los campos son obligatorios.', 'reintentoDatos' => $_POST]);
        exit;
    }

    // Comprobamos si hay conflicto con otra circular en las fechas
    $sqlCheck = "SELECT id_file, titulo, fecha_inicio_actividad, fecha_fin_actividad 
                 FROM circulares 
                 WHERE rama = :rama 
                   AND id_file != :id_file
                   AND (
                     (fecha_inicio_actividad <= :fecha_fin AND fecha_fin_actividad >= :fecha_inicio)
                   ) 
                 LIMIT 1";

    $stmtCheck = $connect->prepare($sqlCheck);
    $stmtCheck->bindParam(':rama', $rama);
    $stmtCheck->bindParam(':fecha_inicio', $fecha_inicio);
    $stmtCheck->bindParam(':fecha_fin', $fecha_fin);
    $stmtCheck->bindParam(':id_file', $id_file);
    $stmtCheck->execute();

    if ($stmtCheck->rowCount() > 0) {
        $existe = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            'error' => true,
            'message' => "Ya existe una circular para la rama {$rama} entre el " .
                         date("d/m/Y", strtotime($existe['fecha_inicio_actividad'])) . " y el " .
                         date("d/m/Y", strtotime($existe['fecha_fin_actividad'])) . " ({$existe['titulo']}).",
            'reintentoDatos' => $_POST
        ]);
        exit;
    }

    $archivo_path = null;

    if (!empty($_FILES['archivo']['tmp_name'])) {
        $archivo = $_FILES['archivo'];
        $archivoTmp = $archivo['tmp_name'];
        $tituloLimpio = preg_replace("/[^a-zA-Z0-9]+/", "-", $titulo);
        $fechaLimpia = str_replace('-', '', $fecha_inicio);
        $archivoNombre = $tituloLimpio . '-' . $fechaLimpia . '.pdf';
        $destino = '/var/www/gruposcout.online/public_html/Modules/Circulares/Pdfs/' . $archivoNombre;

        if (file_exists($destino)) {
            echo json_encode([
                'error' => true,
                'message' => 'El archivo ya existe en el servidor.',
                'reintentoDatos' => $_POST
            ]);
            exit;
        }

        if (!move_uploaded_file($archivoTmp, $destino)) {
            echo json_encode([
                'error' => true,
                'message' => 'Error al mover el archivo.',
                'reintentoDatos' => $_POST
            ]);
            exit;
        }

        $archivo_path = "https://gruposcout.online/Modules/Circulares/Pdfs/" . $archivoNombre;
    }

    $sql = "UPDATE circulares 
            SET titulo = :titulo, 
                fecha_inicio_actividad = :fecha_inicio,
                fecha_fin_actividad = :fecha_fin,
                pernoctas = :pernoctas,
                ubicacion = :ubicacion,
                rama = :rama";

    if ($archivo_path) {
        $sql .= ", archivo_path = :archivo_path";
    }

    $sql .= " WHERE id_file = :id_file";

    $stmt = $connect->prepare($sql);
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_fin', $fecha_fin);
    $stmt->bindParam(':pernoctas', $pernoctas);
    $stmt->bindParam(':ubicacion', $ubicacion);
    $stmt->bindParam(':rama', $rama);
    $stmt->bindParam(':id_file', $id_file, PDO::PARAM_INT);

    if ($archivo_path) {
        $stmt->bindParam(':archivo_path', $archivo_path);
    }

    if ($stmt->execute()) {
        echo json_encode([
            'error' => false,
            'message' => 'La circular fue actualizada con éxito.'
        ]);
    } else {
        echo json_encode([
            'error' => true,
            'message' => 'Hubo un error al actualizar la circular.',
            'reintentoDatos' => $_POST
        ]);
    }
}
