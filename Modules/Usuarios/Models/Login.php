<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    include_once ("../../../Models/ConnectDatabase.php");
    require_once '../../Global/Models/Token.php';
    include_once "../Models/Usuario.php";
    include_once "../../Roles/Models/Rol.php";
    use Mailer\Templates;


    function login1($connect, $mailManager) {
        $mail = $_GET['email'];
        $password = $_GET['password'];

        // Prepare the SQL statement with placeholders
        $sql = "SELECT * FROM usuarios WHERE correo = :mail";
        $stmt = $connect->prepare($sql);
        $stmt->bindParam(':mail', $mail, PDO::PARAM_STR);
        $stmt->execute();
    
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        if ($data[0]['verificado'] == 0 && $data[0]['eliminado'] == 0) {
            // Enviar correo de verificación
            $nuevo_token = Token::create($data[0]);
            $errorMail = reenviarMail($mailManager, $data[0]['correo'], $data[0]['nombre'], $nuevo_token);

            if ($errorMail) {
                echo json_encode(['error' => true, 'message' => 'Correo electrónico no accesible, inténtelo más tarde.']);
            } else {
                echo json_encode(['error' => true, 'message' => 'Correo electrónico no verificado. Revisa tu bandeja de entrada o spam.']);
            }
            return;
        } else if ($data[0]['eliminado'] == 1) {
            echo json_encode(['error' => true, 'message' => 'Correo electrónico no encontrado.']);
            return;
        }
    
        // Verificar la contraseña
        if (password_verify($password, $data[0]['password'])) {
            // Guardar el ID del usuario en la sesión
            $_SESSION['user_id'] = $data[0]['id_usr'];  // Guardar el ID del usuario
    
            // Enviar el correo de doble factor
            enviarDobleFactor($mailManager, $data[0]['correo'], $data[0]['nombre']);
            
            // Responder que se debe pasar al paso de doble factor
            echo json_encode(['error' => false, 'status' => 'factor']);
        } else {
            echo json_encode(['error' => true, 'message' => 'Contraseña incorrecta.']);
        }
    }
    
    

    function login2($connect, $mailManager) {    
        // Recuperar el ID del usuario desde la sesión
        $userId = $_SESSION['user_id'] ?? null;
        $codigoIngresado = $_POST['codigo'] ?? null;
        $codigoCookie = $_COOKIE['verificacion_code'] ?? null;
    
        // Respuesta unificada para todo el proceso
        $response = [
            'error' => true,  // Por defecto, todo será un error hasta que se valide correctamente
            'status' => 'error',
            'message' => 'El código de verificación ha expirado o no es válido.',
        ];
    
        // Verificar si el código ingresado es válido y si la cookie existe
        if ($codigoIngresado && $codigoCookie) {
            if ($codigoIngresado == $codigoCookie) {
                // Recuperar los datos completos del usuario desde la base de datos usando el ID
                $stmt = $connect->prepare("SELECT * FROM usuarios WHERE id_usr = :user_id");
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
    
                // Obtener los datos del usuario
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if (!$data) {
                    // Si no se encontró el usuario en la base de datos, devolver error
                    $response['message'] = 'Usuario no encontrado.';
                } else {
                    // Si los datos existen, crear el objeto Usuario
                    $user = new Usuario($data);
                    // Generar el nuevo token

                    $token = Token::createToken([
                        'user_id' => $data['id_usr'],
                        'correo' => $data['correo'],
                        'nombre' => $data['nombre'],
                        'type' => 'autologin'
                    ]);
                    
                    // Calcula expiración (debe ir en sincrónía con la expiración del token JWT)
                    $exp = time() + 60 * 60 * 24 * 30; // 30 días
                    
                    // Setea cookie
                    setcookie("user_session", $token, $exp, "/", ".gruposcout.online", true, true);
                    

                    $rolInfo = sacarInfoRolLogIn($data['id_rol'], $connect);
                    $user->setRol($rolInfo);

                    if ($user->getIdRol() >= 2) {
                        $_SESSION['rama'] = [
                            'idRol' => $user->getIdRol(),
                            'rama' => sacarInfoMonitor($data['id_usr'], $connect)
                        ];
                    }
    
                    // Guardar en la sesión los datos del usuario
                    $_SESSION['user_id'] = $user->getIdUsr();
                    $_SESSION['user'] = serialize($user);
    
                    // Enviar correo confirmando el login
                    enviarCorreo($mailManager, $user->getCorreo(), $user->getNombre(), $user->getIdRol());
    
                    // Respuesta exitosa, login completado
                    $response = [
                        'error' => false,
                        'status' => 'success',
                        'message' => 'Login exitoso. Bienvenido.',
                    ];
                }
            } else {
                // Si el código es incorrecto
                $response['message'] = 'El código es incorrecto. Intenta de nuevo.';
            }
        }
    
        // Si no se ingresó código o la cookie ha expirado
        echo json_encode($response);
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

    function enviarCorreo($mailManager, $to, $name, $idRol) {
        // Parámetros para el template
        $params = [
            'nombre' => $name,
            'rol' => $idRol,
        ];
        
        //Recoger el template que necesito
        $template = Templates::getTemplate('inicio_sesion', $params);
        //Unir el body con el footer
        $body = $template['body'] . Templates::getDefaultFooter();
        //Coger el subject del correo
        $subject = $template['subject'];
        //Unir y enviar todo al usuario
        return $mailManager->sendMail($to, $subject, $body, true);
    }    

    function reenviarMail ($mailManager, $to, $name, $token) {
        // Parámetros para el template
        $params = [
            'nombre' => $name,
            'token' => $token
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

    function enviarDobleFactor ($mailManager, $to, $name) {
        //Crear codigo de 6 digitos aleatorio
        $codigo = rand(100000, 999999);
        setcookie("verificacion_code", $codigo, time() + 300, "/"); // 300 segundos = 5 minutos
        // Parámetros para el template
        $params = [
            'nombre' => $name,
            'codigo' => $codigo
        ];
        //Recoger el template que necesito
        $template = Templates::getTemplate('doble_factor', $params);
        //Unir el body con el footer
        $body = $template['body'] . Templates::getDefaultFooter();
        //Coger el subject del correo
        $subject = $template['subject'];
        //Unir y enviar todo al usuario
        return $mailManager->sendMail($to, $subject, $body, true);
    }