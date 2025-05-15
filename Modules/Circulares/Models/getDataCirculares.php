<?php

function getNotDeleteCirculares($connect) {
    $query = "SELECT *
            FROM circulares 
            WHERE eliminado_por IS NULL AND enviado = 1
            ORDER BY fecha_subida DESC";

    $stmt = $connect->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getDataCirculares($conn) {

    if (!isset($_SESSION['rama'])) {
        return []; // No permitir acceso si no hay sesión de rama
    }

    $idRol = $_SESSION['rama']['idRol'];
    $rama = $_SESSION['rama']['rama'];

    // Construimos la consulta base
    $sql = "
        SELECT 
            c.id_file,
            c.titulo,
            c.fecha_inicio_actividad,
            c.fecha_fin_actividad,
            c.pernoctas,
            c.archivo_path,
            c.rama,
            c.ubicacion,
            u.nombre AS creador_nombre,
            u.apellidos AS creador_apellidos,
            ue.nombre AS eliminador_nombre,
            ue.apellidos AS eliminador_apellidos,
            c.fecha_subida,
            c.enviado,
            c.eliminado
        FROM circulares c
        LEFT JOIN usuarios u ON c.creado_por = u.id_usr
        LEFT JOIN usuarios ue ON c.eliminado_por = ue.id_usr
    ";

    $params = [];

    if ($idRol == 3 || $idRol == 4) {
        // Si es rol 3 o 4, filtrar por rama
        $sql .= " WHERE c.rama = :rama";
        $params[':rama'] = $rama;
    } elseif ($idRol == 5) {
        // Si es rol 5 (admin o similar), no se filtra nada
    } else {
        // Rol 1 o 2 no puede acceder
        return [];
    }

    // Preparamos la consulta
    $stmt = $conn->prepare($sql);

    // Bind de parámetros solo si hay
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }

    // Ejecutamos
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

