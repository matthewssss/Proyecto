<?php  
session_start();
require_once '../Models/Token.php';
require_once '../../../Models/ConnectDatabase.php';
require_once __DIR__ . '/../../../Vendor/Backend/autoload.php';
require_once '../../../Mail/MailManager.php';
require_once '../../../Mail/Templates.php';

use Mailer\Templates;
// Crear el objeto de MailManager
$mailManager = new MailManager();

require_once '../../Usuarios/Models/Usuario.php'; // Clase usuario
require_once '../../Roles/Models/Rol.php'; // Clase rol

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$connect = $conexion;

header('Content-Type: application/json');

$token = $_GET['token'] ?? '';
$type = $_GET['type'] ?? '';
$email = $_GET['email'] ?? '';
$response = ['success' => false];

// Validación mínima inicial
if (empty($type) || ($type !== 'resend_email' && empty($token))) {
    echo json_encode(['success' => false, 'code' => 'missing_data', 'message' => 'Faltan datos necesarios']);
    exit;
}

try {
    switch ($type) {
        case 'verify':
            if (!Token::isValid($token, $type)) {
                throw new Exception('Token no válido o mal formado.', 400);
            }

            $userData = Token::getUserByToken($token);
            $usuario = buscarUsuarioPorId($userData['id'], $connect);

            if (!$usuario) {
                $response = ['success' => false, 'code' => 'user_not_found', 'message' => 'Usuario no encontrado'];
                break;
            }

            if ($usuario['verificado']) {
                $response = ['success' => false, 'code' => 'already_verified', 'message' => 'El correo ya ha sido verificado'];
            } else {
                $stmt = $connect->prepare("UPDATE usuarios SET verificado = 1, token = NULL WHERE id_usr = :id");
                $stmt->bindParam(':id', $userData['id']);
                $stmt->execute();

                $token = Token::create([
                    'user_id' => $usuario['id_usr'],
                    'correo' => $usuario['correo'],
                    'nombre' => $usuario['nombre'],
                    'type' => 'autologin'
                ]);
            
                $fullName = trim("{$usuario['nombre']} {$usuario['apellidos']}");
                $mailError = enviarCorreoBienvenida($mailManager, $usuario['correo'], $fullName, $token);

                $response = ($mailError === false
                ? ['success' => true, 'status' => 'correo_verificado', 'message' => 'Tu cuenta ha sido verificada con éxito. Puede iniciar sesion directamente desde su correo', 'title' => '¡Correo verificado!']
                : ['success' => false, 'code' => 'error_mail', 'message' => 'No se ha podido enviar un correo de bienvenida, puede iniciar sesion y seguir disfrutando de la experiencia.']);
            }
            break;

        case 'autologin':
            if (!Token::isValid($token, $type)) {
                throw new Exception('Token no válido o mal formado.', 400);
            }

            $userData = Token::getUserByToken($token);
            $usuario = buscarUsuarioPorId($userData['id'], $connect);

            if (!$usuario) {
                $response = ['success' => false, 'code' => 'user_not_found', 'message' => 'Usuario no encontrado'];
                break;
            }

            if (!$usuario['verificado']) {
                $response = ['success' => false, 'code' => 'not_verified', 'message' => 'Usuario no verificado'];
            } else {
                $usuarioObj = new Usuario($usuario);
                $rolInfo = sacarInfoRolLogIn($usuario['id_rol'], $connect);
                $usuarioObj->setRol($rolInfo);

                if ($usuarioObj->getIdRol() >= 2) {
                    $_SESSION['rama'] = [
                        'idRol' => $usuarioObj->getIdRol(),
                        'rama' => sacarInfoMonitor($usuario['id_usr'], $connect)
                    ];
                }

                $_SESSION['user_id'] = $usuarioObj->getIdUsr();
                $_SESSION['user'] = serialize($usuarioObj);

                $response = [
                    'success' => true,
                    'status' => 'autologin_ok',
                    'message' => 'Ya puedes navegar e investigar cada rincon de nuestra web...👀',
                    'title' => '!Sesion iniciada!'
                ];
            }
            break;

        case 'resend_email':
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response = ['success' => false, 'code' => 'invalid_email', 'message' => 'Formato de correo inválido'];
                break;
            }

            $usuario = buscarUsuarioPorCorreo($email, $connect);

            if (!$usuario) {
                $response = ['success' => false, 'code' => 'user_not_found', 'message' => 'No se ha encontrado ningún usuario con ese correo'];
                break;
            }

            if ($usuario['verificado']) {
                $response = ['success' => false, 'code' => 'already_verified', 'message' => 'Este correo ya ha sido verificado'];
                break;
            }

            $response = reenviarCorreoVerificacion($usuario, $mailManager);
            break;

        case 'recover':
            if (Token::isValid($token, $type)) {
                $response = ['success' => false, 'code' => 'recover', 'message' => 'Puede recuperar su contraseña'];
            } else {
                throw new Exception('Token no válido o mal formado.', 400);
            }
            
            break;
        default:
            $response = ['success' => false, 'code' => 'invalid_type', 'message' => 'Tipo de validación no reconocido'];
            break;
    }

} catch (\Exception $e) {
    $response = ['success' => false, 'code' => 'invalid_token', 'message' => $e->getMessage()];
}

echo json_encode($response);


// ────────────────────────────────────────────────────────────────────────────────── 

function buscarUsuarioPorId($id, $connect) {
    $stmt = $connect->prepare("SELECT * FROM usuarios WHERE id_usr = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function buscarUsuarioPorCorreo($email, $connect) {
    $stmt = $connect->prepare("SELECT * FROM usuarios WHERE correo = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function reenviarCorreoVerificacion($usuario, $mailManager) {
    $token = Token::create([
        'user_id' => $usuario['id_usr'],
        'correo' => $usuario['correo'],
        'nombre' => $usuario['nombre'],
        'type' => 'verify'
    ]);

    $fullName = trim("{$usuario['nombre']} {$usuario['apellidos']}");
    $mailError = enviarCorreoVerificacion($mailManager, $usuario['correo'], $fullName, $token);

    return $mailError === false
        ? ['success' => true, 'message' => 'Correo reenviado correctamente.']
        : ['success' => false, 'code' => 'send_failed', 'message' => 'Error al reenviar el correo'];
}


function sacarInfoRolLogIn($idRol, $connect) {
    try {
        $query = "SELECT * FROM roles WHERE id_rol = ?";
        $stmt = $connect->prepare($query);
        $stmt->execute([$idRol]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : false;
    } catch (PDOException $e) {
        return false;
    }
}

function sacarInfoMonitor($idUsr, $connect) {
    try {
        $query = "SELECT * FROM monitores WHERE id_usr = ?";
        $stmt = $connect->prepare($query);
        $stmt->execute([$idUsr]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['rama'];
    } catch (PDOException $e) {
        return false;
    }
}


function enviarCorreoVerificacion($mailManager, $to, $name, $token) {
    $params = ['nombre' => $name, 'token' => $token, 'correo' => $to];
    $template = Templates::getTemplate('verifica_cuenta', $params);
    $body = $template['body'] . Templates::getDefaultFooter();
    $subject = $template['subject'];
    return $mailManager->sendMail($to, $subject, $body, true);
}


function enviarCorreoBienvenida($mailManager, $to, $name, $token) {
    $params = ['nombre' => $name, 'token' => $token, 'correo' => $to];     
    $template = Templates::getTemplate('bienvenida', $params);
    $body = $template['body'] . Templates::getDefaultFooter();
    $subject = $template['subject'];
    return $mailManager->sendMail($to, $subject, $body, true);
}


