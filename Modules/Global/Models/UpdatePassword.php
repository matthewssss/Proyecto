<?php
require_once '../../../Models/ConnectDatabase.php';
require_once '../../../Mail/Templates.php';
require_once '../../../Mail/MailManager.php';
require_once 'Token.php';

header('Content-Type: application/json');
use Mailer\Templates;
$connect = $conexion;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido', 405);
    }
    if (!isset($_POST['token'], $_POST['password'])) {
        throw new Exception('Faltan parámetros', 400);
    }

    $token = $_POST['token'];
    $newPassword = $_POST['password'];

    $user = Token::getUserByToken($token);

    if (!$user || !isset($user['id'])) {
        throw new Exception('Token inválido o expirado', 401);
    }

    $hashed = password_hash($newPassword, PASSWORD_BCRYPT, ["cost" => 15]);

    $stmt = $connect->prepare("UPDATE usuarios SET password = :password, token = NULL, token_expiration = 0 WHERE id_usr = :id AND eliminado = 0");
    $stmt->bindParam(':password', $hashed);
    $stmt->bindParam(':id', $user['id']);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        throw new Exception('No se pudo actualizar la contraseña o ya estaba establecida', 400);
    }
    enviarCorreoConfirmacion($user['email'], $user['name']);
    echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}



function enviarCorreoConfirmacion($to, $name) {
    $mailManager = new MailManager();
    // Parámetros para el template
    $params = [
        'nombre' => $name,
    ];
    
    //Recoger el template que necesito
    $template = Templates::getTemplate('nueva_contraseña', $params);
    //Unir el body con el footer
    $body = $template['body'] . Templates::getDefaultFooter();
    //Coger el subject del correo
    $subject = $template['subject'];
    //Unir y enviar todo al usuario
    return $mailManager->sendMail($to, $subject, $body, true);
}