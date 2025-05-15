<?php

use Mailer\Templates;

function updateUsersRol($connect, $mailManager) {
    $data = isset($_POST['cambios']) ? json_decode($_POST['cambios'], true) : [];

    $response = [];
    $error = false;
    $msg = '';
    $rolOptions = [
        1 => 'Padre',
        2 => 'Monitor',
        3 => 'Secretario Unidad',
        4 => 'Coordinador Unidad',
        5 => 'Admin'
    ];
    

    foreach ($data as $usuario) {
        $id = intval($usuario['id']);
        $rol = intval($usuario['rol']);
        $rama = isset($usuario['rama']) ? trim($usuario['rama']) : null;
    
        // Actualizamos el rol
        $updateRol = $connect->prepare("UPDATE usuarios SET id_rol = :rol WHERE id_usr = :id");
        $updateRol->bindParam(':rol', $rol, PDO::PARAM_INT);
        $updateRol->bindParam(':id', $id, PDO::PARAM_INT);
        $updateRol->execute();
    
        // Gestión de rama solo si NO es Padre (rol 1)
        if ($rol !== 1) {
            $check = $connect->prepare("SELECT COUNT(*) FROM monitores WHERE id_usr = :id");
            $check->bindParam(':id', $id, PDO::PARAM_INT);
            $check->execute();
            $existe = $check->fetchColumn();
    
            if ($existe) {
                $updateRama = $connect->prepare("UPDATE monitores SET rama = :rama WHERE id_usr = :id");
                $updateRama->bindParam(':rama', $rama, PDO::PARAM_STR);
                $updateRama->bindParam(':id', $id, PDO::PARAM_INT);
                $updateRama->execute();
            } else {
                $insertRama = $connect->prepare("INSERT INTO monitores (id_usr, rama) VALUES (:id, :rama)");
                $insertRama->bindParam(':id', $id, PDO::PARAM_INT);
                $insertRama->bindParam(':rama', $rama, PDO::PARAM_STR);
                $insertRama->execute();
            }

            // Obtener el nombre del usuario
            $selectUser = $connect->prepare("SELECT nombre, correo FROM usuarios WHERE id_usr = :id");
            $selectUser->bindParam(':id', $id, PDO::PARAM_INT);
            $selectUser->execute();
            $userInfo = $selectUser->fetch(PDO::FETCH_ASSOC);
            
            $name = $userInfo['nombre'];
            $correo = $userInfo['correo'];
            $rolTexto = $rolOptions[$rol] ?? 'Rol desconocido';
            // Enviar el correo de notificación si no es Padre
            $errorMail = enviarCambio($mailManager, $correo, $name, $rama, $rolTexto);

            if ($errorMail) {
                $msg .=  '<li>Error al notificar a ' . $correo . '</li>';
                $error = true;
            }

        } else {
            // Si es Padre, desactivar rama (poniendo '0' para que el trigger lo elimine)
            $deleteRama = $connect->prepare("DELETE FROM monitores WHERE id_usr = :id");
            $deleteRama->bindParam(':id', $id, PDO::PARAM_INT);
            $deleteRama->execute();
        }


        
    
        $response[] = [
            'msg' => $msg,
            'error' => $error
        ];
    }
    

    echo json_encode($response);
}


function enviarCambio($mailManager, $to, $name, $rama, $idRol) {
    // Parámetros para el template
    $params = [
        'nombre' => $name,
        'rama' => $rama,
        'nuevo_rol' => $idRol,
    ];
    
    //Recoger el template que necesito
    $template = Templates::getTemplate('cambio_rol_rama', $params);
    //Unir el body con el footer
    $body = $template['body'] . Templates::getDefaultFooter();
    //Coger el subject del correo
    $subject = $template['subject'];
    //Unir y enviar todo al usuario
    return $mailManager->sendMail($to, $subject, $body, true);
}   