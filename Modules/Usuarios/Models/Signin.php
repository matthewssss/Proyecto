<?php

use Mailer\Templates;

function signin($connect, $mailManager) {
    try {
        $name = $_POST['name'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $tlf = $_POST['tlf'];
        $password = password_hash($_POST['pass'], PASSWORD_BCRYPT, ["cost" => 15]);

        // Insertar el nuevo usuario
        $sql = "INSERT INTO usuarios (nombre, apellidos, correo, tel, password, id_rol) VALUES (:name, :lastname, :email, :tlf, :password, 1)";
        $stmt = $connect->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':lastname', $lastname, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':tlf', $tlf, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $userId = $connect->lastInsertId(); // ID del nuevo usuario

            $token = Token::create([
                'user_id' => $userId,
                'correo' => $email,
                'nombre' => "$name $lastname",
                'type' => 'verify'
            ]);

            // Enviar correo con el token
            $fullName = $name . ' ' . $lastname;
            $mailError = enviarCorreoVerificacion($mailManager, $email, $fullName, $token);

            if ($mailError) {
                // Si falla el correo, eliminar al usuario
                $deleteStmt = $connect->prepare("DELETE FROM usuarios WHERE id_usr = :id");
                $deleteStmt->bindParam(':id', $userId);
                $deleteStmt->execute();

                return json_encode(['error' => true, 'message' => 'Error al enviar el correo de verificación. Inténtelo de nuevo.']);
            } else {
                return json_encode(['error' => false, 'message' => 'Cuenta creada correctamente. Verifique su correo.']);
            }
        } else {
            return json_encode(['error' => true, 'message' => 'Error al registrar la cuenta.']);
        }

    } catch (Exception $exception) {
        return json_encode(['error' => true, 'message' => $exception->getMessage()]);
    }
}



function enviarCorreoVerificacion($mailManager, $to, $name, $token) {
    // Parámetros para el template
    $params = [
        'nombre' => $name,
        'token' => $token,
        'correo' => $to
    ];
    
    //Recoger el template que necesito
    $template = Templates::getTemplate('verifica_cuenta', $params);
    //Unir el body con el footer
    $body = $template['body'] . Templates::getDefaultFooter();
    //Coger el subject del correo
    $subject = $template['subject'];
    //Unir y enviar todo al usuario
    return $mailManager->sendMail($to, $subject, $body, true);
}