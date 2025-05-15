<?php

use Mailer\Templates;

function updateData($connect, $mailManager) {

    $esPerfil = isset($_POST['profile']) && $_POST['profile'] == 'true';

    $idUsr = $esPerfil ? unserialize($_SESSION["user"])->getIdUsr() : ($_POST['id'] ?? null);

    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? null;
    $rama = $_POST['rama'] ?? null;

    // Validación básica
    if (!$idUsr || empty($nombre) || empty($apellidos) || empty($correo) || empty($telefono)) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
        exit;
    }

    try {
        $usuarioAntes = obtenerDatosUsuario($connect, $idUsr);

        // Actualización general
        if (!empty($password)) {
            $passHash = password_hash($password, PASSWORD_BCRYPT, ["cost" => 15]);
            $sql = "UPDATE usuarios 
                    SET nombre = :nombre, apellidos = :apellidos, correo = :correo, tel = :telefono, password = :password 
                    WHERE id_usr = :idUsr";
            $stmt = $connect->prepare($sql);
            $stmt->bindParam(":password", $passHash);
        } else {
            $sql = "UPDATE usuarios 
                    SET nombre = :nombre, apellidos = :apellidos, correo = :correo, tel = :telefono 
                    WHERE id_usr = :idUsr";
            $stmt = $connect->prepare($sql);
        }

        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellidos", $apellidos);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":idUsr", $idUsr);
        $stmt->execute();

        // Si NO es perfil (viene desde admin), gestionamos rol y monitores
        if (!$esPerfil && $rol !== null) {
            // Actualizar rol
            $stmtRol = $connect->prepare("UPDATE usuarios SET id_rol = :rol WHERE id_usr = :idUsr");
            $stmtRol->bindParam(":rol", $rol);
            $stmtRol->bindParam(":idUsr", $idUsr);
            $stmtRol->execute();

            // Gestionar monitor
            if (in_array($rol, [2, 3, 4, 5])) {
                // Insertar/Actualizar monitor
                $stmtRama = $connect->prepare("UPDATE monitores SET rama = :rama WHERE id_usr = :id");
                $stmtRama->bindParam(':rama', $rama, PDO::PARAM_STR);
                $stmtRama->bindParam(':id', $id, PDO::PARAM_INT);
                $stmtRama->execute();
            } else {
                // Eliminar de monitores si ya no tiene rol de monitor
                $stmtDel = $connect->prepare("DELETE FROM monitores WHERE id_usr = :idUsr");
                $stmtDel->bindParam(":idUsr", $idUsr);
                $stmtDel->execute();
            }

            // Recoger los datos actualizados tras los cambios para comparar
            $usuarioDespues = obtenerDatosUsuario($connect, $idUsr);

            // Comparar y generar cambios (nombre, correo, etc.)
            $cambios = obtenerCambios($usuarioAntes, $usuarioDespues, $rol, $rama, $password, $password);

            // Enviar correo si hay cambios relevantes
            if (!empty($cambios)) {
                enviarCorreoNotificacion($mailManager, $usuarioDespues['correo'], $usuarioDespues['nombre'], $cambios);
            }
        }


        // Si viende desde el perfil, actualizar el usuario en sesión
        if ($esPerfil) {
            $stmtUser = $connect->prepare("SELECT * FROM usuarios WHERE id_usr = :idUsr");
            $stmtUser->bindParam(":idUsr", $idUsr);
            $stmtUser->execute();

            $data = $stmtUser->fetch(PDO::FETCH_ASSOC);
            $user = new Usuario($data);
            $_SESSION["user"] = serialize($user);
        }


        echo json_encode(['success' => true, 'message' => 'Datos actualizados correctamente.']);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

}

function obtenerCambios($antes, $despues, $rolNuevo, $rama, $password, $passwordTextoClaro = null) {
    $cambios = [];
    $isPadre = ($rolNuevo == 1);

    if ($antes['nombre'] !== $despues['nombre']) {
        $cambios[] = "🎭 Nombre: <strong>{$antes['nombre']}</strong> → <strong>{$despues['nombre']}</strong>";
    }
    if ($antes['apellidos'] !== $despues['apellidos']) {
        $cambios[] = "💼 Apellidos: <strong>{$antes['apellidos']}</strong> → <strong>{$despues['apellidos']}</strong>";
    }
    if (!$isPadre && $antes['id_rol'] != $rolNuevo) {
        $cambios[] = "🧩 Rol: <strong>" . obtenerNombreRol($antes['id_rol']) . "</strong> → <strong>" . obtenerNombreRol($rolNuevo) . "</strong>";
    }
    if (!$isPadre && isset($rama)) {
        $cambios[] = "🌿 Rama asignada: <strong>$rama</strong>";
    }
    if (!empty($passwordTextoClaro)) {
        $cambios[] = "🔐 Tu nueva contraseña es: <strong>{$passwordTextoClaro}</strong>";
    }
    

    return $cambios;
}

function obtenerNombreRol($id) {
    $rolOptions = [
        1 => 'Padre',
        2 => 'Monitor',
        3 => 'Secretario Unidad',
        4 => 'Coordinador Unidad',
        5 => 'Admin'
    ];

    return $rolOptions[$id] ?? 'Desconocido';
}

function obtenerDatosUsuario($connect, $idUsr) {
    $stmt = $connect->prepare("SELECT * FROM usuarios WHERE id_usr = :idUsr");
    $stmt->bindParam(":idUsr", $idUsr);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function enviarCorreoNotificacion($mailManager, $to, $name, $changes) {
    $params = [
        'nombre' => $name
    ];
    
    $template = Templates::getTemplate('actualizacion_datos_admin', $params);
    $templateFinal = Templates::getTemplate('actualizacion_datos_admin_final');

    $body = $template['body'];
    $body .= "<ul>";
    foreach ($changes as $cambio) {
        $body .= "<li>$cambio</li>";
    }
    $body .= "</ul>";
    $body .= $templateFinal['body'];

    // Footer genérico
    $body .= Templates::getDefaultFooter();

    return $mailManager->sendMail($to, $template['subject'], $body, true);
}
