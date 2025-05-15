<?php 


function getMonitoresFiltrados($connect) {
    $idUsr = $_SESSION['user_id'];
    $usr = unserialize($_SESSION['user']);
    $idRol = $usr->getIdRol();

    $ramaList = [];

    // Si es padre, sacamos todas las unidades de sus hijos
    if ($idRol === 1) {
        $unidadStmt = $connect->prepare("
            SELECT DISTINCT i.unidad 
            FROM inscripcion_asociados i 
            WHERE i.eliminado = 0 AND i.id_usr = :id
        ");
        $unidadStmt->bindParam(':id', $idUsr, PDO::PARAM_INT);
        $unidadStmt->execute();
        $ramaList = $unidadStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($ramaList)) {
            return json_encode([
                'error' => true,
                'message' => 'No tienes ningún hijo/a inscrito. Regístralo primero para ver los monitores asignados.'
            ]);
        }
    }

    // Obtener todos los usuarios
    $usuariosStmt = $connect->prepare("SELECT id_usr, nombre, apellidos, correo, tel, id_rol FROM usuarios");
    $usuariosStmt->execute();
    $usuarios = $usuariosStmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener monitores
    if ($idRol === 1) {
        // Monitores de todas las ramas de los chavales apuntados con su user
        $placeholders = implode(',', array_fill(0, count($ramaList), '?'));
        $monitoresQuery = "SELECT id_usr, rama FROM monitores WHERE rama IN ($placeholders)";
        $monitoresStmt = $connect->prepare($monitoresQuery);
        foreach ($ramaList as $index => $rama) {
            $monitoresStmt->bindValue($index + 1, $rama, PDO::PARAM_STR);
        }
    } else {
        // Rol mayor a 1 ve todos
        $monitoresQuery = "SELECT id_usr, rama FROM monitores";
        $monitoresStmt = $connect->prepare($monitoresQuery);
    }

    $monitoresStmt->execute();
    $monitores = $monitoresStmt->fetchAll(PDO::FETCH_ASSOC);

    // Mapeo de roles
    $rolOptions = [
        1 => 'Padre',
        2 => 'Monitor',
        3 => 'Secretario Unidad',
        4 => 'Coordinador Unidad',
        5 => 'Admin'
    ];

    $tablaHtml = '';
    foreach ($monitores as $monitor) {
        $infoUsr = array_filter($usuarios, fn($u) => $u['id_usr'] == $monitor['id_usr']);
        $infoUsr = reset($infoUsr);

        if (!$infoUsr) continue;

        $tablaHtml .= '
            <tr>
                <td>' . htmlspecialchars($infoUsr['nombre'] . ' ' . $infoUsr['apellidos']) . '</td>
                <td>' . htmlspecialchars($infoUsr['correo']) . '</td>
                <td>' . htmlspecialchars($infoUsr['tel']) . '</td>
                <td>' . htmlspecialchars($rolOptions[$infoUsr['id_rol']] ?? 'Desconocido') . '</td>
                <td>' . htmlspecialchars($monitor['rama']) . '</td>
            </tr>
        ';
    }

    return json_encode(['error' => false, 'html' => $tablaHtml]);
}

