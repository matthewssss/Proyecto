<?php

session_start();
require_once "../Modules/Usuarios/Models/Usuario.php";
require_once "../Modules/Roles/Models/Rol.php";
require_once "ConnectDatabase.php";
require_once "../Modules/Global/Models/Token.php";

Token::init();
$response = ['error' => true];
$pdo = $conexion;
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_session'])) {
    $token = $_COOKIE['user_session'];
    if (Token::isValid($token, 'autologin')) {
        $userData = Token::getUserByToken($token, 'autologin');

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usr = ?");
        $stmt->execute([$userData['id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $user = new Usuario($usuario);
            $rolInfo = sacarInfoRol($usuario['id_rol'], $pdo);
            $user->setRol($rolInfo);

            // Cargar rama si es monitor
            if ($user->getIdRol() >= 2) {
                $_SESSION['rama'] = [
                    'idRol' => $user->getIdRol(),
                    'rama' => sacarInfoMonitor($user->getIdUsr(), $pdo)
                ];
            }

            $_SESSION['user'] = serialize($user);
            $_SESSION['user_id'] = $user->getIdUsr();

            // Decodificar para mirar la expiración
            $tiempoRestante = Token::timeLeft($token);
            // Renovar si quedan 3 días o menos
            if ($tiempoRestante <= 259200) {
                $nuevoToken = Token::createToken([
                    'user_id' => $user->getIdUsr(),
                    'correo' => $user->getCorreo(),
                    'nombre' => $user->getNombre(),
                    'type' => 'autologin'
                ]);

                setcookie("user_session", $nuevoToken, time() + (60 * 60 * 24 * 30), "/", ".gruposcout.online", true, true);
            }
            $response = ['error' => false];
        }
    } else {
        // Token no válido → eliminar cookie
        setcookie("user_session", "", time() - 3600, "/", ".gruposcout.online", true, true);
    }
} elseif (isset($_SESSION['user_id'])) {
    $response = ['error' => false];
}

echo json_encode($response);

function sacarInfoRol($idRol, $connect) {
    $stmt = $connect->prepare("SELECT * FROM roles WHERE id_rol = ?");
    $stmt->execute([$idRol]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function sacarInfoMonitor($idUsr, $connect) {
    try {
        $stmt = $connect->prepare("SELECT * FROM monitores WHERE id_usr = ?");
        $stmt->execute([$idUsr]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['rama'] ?? null;
    } catch (PDOException $e) {
        return null;
    }
}
