<?php    
    session_start();
    include_once "../../../Models/ConnectDatabase.php"; //Conexion a la bd;
    require_once '../../../Mail/MailManager.php'; //Gestor para enviar correos
    require_once '../../../Mail/Templates.php'; //Plantillas de los correos
    require_once '../../Global/Models/Token.php'; //Clase para generar tokens
    
    include_once "../Models/Usuario.php"; // Clase Usuario
    include_once "../../Roles/Models/Rol.php"; // Clase Rol
    
    include_once "../Models/Login.php"; //Login
    include_once "../Models/Signin.php"; //Sign in
    include_once "../Models/GetMail.php"; //Coger mail

    include_once "loadButtons.php"; //Carga de botones
    include_once "../Models/GetData.php"; //Traer datos del usr
    include_once "../Models/UpdateData.php"; //Actualizar datos del usr
    include_once "../Models/GetUsers.php"; //Coger usuarios
    include_once "../Models/updateUsersRol.php"; //Actualizar usuarios
    include_once "../Models/DeleteUser.php"; //Eliminar usuarios
    include_once "../Models/GetMonitoresData.php"; //Recoger monitores

    use Mailer\Templates; //Poder usar los templates
    // Crear el objeto de MailManager
    $mailManager = new MailManager();
    //Pilla la conexion del connect database
    $connect = $conexion;


    // 1️⃣ Veo si quiere eliminar o no ya que 'action' no esta definido
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if ($data['action'] === 'logout') {
            session_unset();
            session_destroy();
    
            // Clear the cookies
            setcookie("user_session", "", time() - 3600, "/", ".gruposcout.online", true, true);
            echo json_encode(['error' => false, 'message' => 'Logged out successfully']);
            exit;
        }
    }

    if (!isset($_GET['action']) && !isset($_POST['action'])) {
        header("HTTP/1.0 404 Forbidden");
        include_once "/404";
        exit();
    }

    if (!isset($_SESSION["user"])) {
        echo json_encode(['sesion' => true]);
        exit();
    }
    
    $action = $_POST['action'] ?? $_GET['action'] ?? null;
    
    // 2️⃣ Si la acción es "checkEmail", verificamos si el correo existe en la BD
    if ($action === 'checkEmail' && isset($_GET['email'])) {
        try {
            $emailExists = getMail($connect, $_GET['email']);
            // Check if the result is an array and has content
            if (is_array($emailExists) && isset($emailExists[0]) && count($emailExists[0]) > 0) {
                if (isset($_GET['recovery']) && $_GET['recovery'] == 'forgot') {
                    sendRecoveryMail($mailManager, $emailExists);
                }
                echo json_encode(['exists' => true, 'message' => 'Correo electronico encontrado']);
            } else {
                echo json_encode(['exists' => false, 'message' => 'Correo electronico no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode([
                'exists' => false,
                'error' => true,
                'message' => 'Error checking email: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    //3️⃣ Si la acción no es "checkEmail", se espera que ya haya sido validado antes
    switch ($action) {
        case 'login':
            echo login1($connect, $mailManager);
            break;
        case 'login2':
            echo login2($connect, $mailManager);
            break;
        case "signin":
            echo signin($connect, $mailManager);
            break;
        case 'getButtons':
            echo botones($connect);
            break;
        case 'campo':
            echo devolverCampo();
            break;
        case 'editData':
            echo editData();
            break;
        case 'updateData':
            echo updateData($connect, $mailManager);
            break;
        case 'getUsers':
            echo getUsersData($connect);
            break;
        case 'updateUsersRol':
            echo updateUsersRol($connect, $mailManager);
            break;
        case 'deleteUser':
            echo json_encode(deleteUser($connect));
            break;
        case 'getMonitoresFiltrados':
            echo getMonitoresFiltrados($connect);
            break;
        default:
            echo json_encode(['error' => true, 'message' => 'Invalid action']);
            break;
    }
    

    function botones ($connect) {
        if (traerDatos($connect)) {
            return devolverBotones();
        } else {
            return json_encode(['error' => true, 'message' => 'No se han encontrado datos']);
        }
    }

    function devolverBotones () {
        if (isset($_SESSION["user"])) {
            return loadButtons();
        } else {
            return json_encode(['error' => true, 'message' => 'No se ha detectado una sesión']);
        }
    }

    function devolverCampo() {
        $campo = $_GET['campoSacar'];
        if (isset($_SESSION["user"])) {
            $usr = unserialize($_SESSION["user"]);
            switch ($campo) {
                case 'id_usr':
                    return json_encode(['error' => false, 'value' => $usr->getIdUsr()]);
                case 'nombre':
                    return json_encode(['error' => false, 'value' => $usr->getNombre()]);
                case 'apellidos':
                    return json_encode(['error' => false, 'value' => $usr->getApellidos()]);
                case 'correo':
                    return json_encode(['error' => false, 'value' => $usr->getCorreo()]);
                case 'tel':
                    return json_encode(['error' => false, 'value' => $usr->getTel()]);
                case 'contraseña':
                    return json_encode(['error' => false, 'value' => $usr->getContraseña()]);
                case 'id_rol':
                    return json_encode(['error' => false, 'value' => $usr->getIdRol()]);
                case 'session_token':
                    return json_encode(['error' => false, 'value' => $usr->getToken()]);
                case 'token_expiration':
                    return json_encode(['error' => false, 'value' => $usr->getTokenExpiration()]);
                default:
                    return json_encode(['error' => true, 'message' => 'Invalid field']);
            }
        } else {
            return json_encode(['error' => true, 'message' => 'No se ha detectado una sesión']);
        }
    }

    function editData () {
        $usr = unserialize($_SESSION["user"]);
        return json_encode($usr -> getEditData());
    }

    function getUsersData($connect) {
        $usuarios = getUsers($connect);
        $monitores = getInfoMonitores($connect);
        
    
        // Mapeamos monitores por ID para acceso rápido
        $ramaPorUsuario = [];
        foreach ($monitores as $monitor) {
            $ramaPorUsuario[$monitor['id_usr']] = $monitor['rama'];
        }
    
        $rolOptions = [
            1 => 'Padre',
            2 => 'Monitor',
            3 => 'Secretario Unidad',
            4 => 'Coordinador Unidad',
            5 => 'Admin'
        ];
    
        $rows = '';
    
        foreach ($usuarios as $user) {
            $id = $user['id_usr'];
            $rama = $ramaPorUsuario[$id] ?? null;
    
            // ROL SELECT
            $rolSelect = '<select class="form-select select-rol">';
            foreach ($rolOptions as $value => $label) {
                $selected = ($value == $user['id_rol']) ? 'selected' : '';
                $rolSelect .= "<option value=\"$value\" $selected>$label</option>";
            }
            $rolSelect .= '</select>';
    
            // RAMA SELECT
            $ramaSelect = '
                <select class="form-select select-rama" ' . ($rama ? '' : 'disabled style="display:none;"') . '>
                    <option value="Lobatos" ' . ($rama == 'Lobatos' ? 'selected' : '') . '>Lobatos</option>
                    <option value="Exploradores" ' . ($rama == 'Exploradores' ? 'selected' : '') . '>Exploradores</option>
                    <option value="Pioneros" ' . ($rama == 'Pioneros' ? 'selected' : '') . '>Pioneros</option>
                    <option value="Rutas" ' . ($rama == 'Rutas' ? 'selected' : '') . '>Rutas</option>
                </select>
            ';

            // Mapeamos los roles por número (en vez de nombre)
            $rolClass = strtolower(str_replace(' ', '', $rolOptions[$user['id_rol']])); // Usar el nombre del rol
            $ramaClass = strtolower($rama ?? '');

            $rows .= "
            <tr class='element-item $rolClass $ramaClass' data-id='" . $id . "' data-rol='" . $user['id_rol'] . "' data-rama='" . ($ramaClass ?? '') . "'> 
                <td>
                    <span class='display-rol'>" . $rolOptions[$user['id_rol']] . "</span>
                    <select class='form-select form-select-sm edit-rol d-none' data-original='" . $user['id_rol'] . "'>
                        ";
            foreach ($rolOptions as $value => $label) {
                $selected = ($value == $user['id_rol']) ? 'selected' : '';
                $rows .= "<option value='$value' $selected>$label</option>";
            }
            $rows .= "</select>
                </td>

                <td>
                    <span class='display-rama'>" . ($rama ?? '') . "</span>
                    <select class='form-select form-select-sm edit-rama d-none' " . ($rama ? '' : 'disabled') . " data-original='" . ($rama ?? '') . "'>
                        <option value='0' disabled " . (!$rama ? 'selected' : '') . ">Seleccione rama</option>    
                        <option value='Lobatos' " . ($rama == 'Lobatos' ? 'selected' : '') . ">Lobatos</option>
                        <option value='Exploradores' " . ($rama == 'Exploradores' ? 'selected' : '') . ">Exploradores</option>
                        <option value='Pioneros' " . ($rama == 'Pioneros' ? 'selected' : '') . ">Pioneros</option>
                        <option value='Rutas' " . ($rama == 'Rutas' ? 'selected' : '') . ">Rutas</option>
                    </select>
                </td>

                <td>{$user['nombre']}</td>
                <td>{$user['apellidos']}</td>
                <td>{$user['correo']}</td>
                <td>{$user['tel']}</td>
                <td class='text-center'>
                    <button class='btn btn-primary btn-sm edit-user' 
                        data-id='{$user['id_usr']}'
                        data-nombre='{$user['nombre']}'
                        data-apellidos='{$user['apellidos']}'
                        data-correo='{$user['correo']}'
                        data-tel='{$user['tel']}'
                        data-rol='{$user['id_rol']}'
                        data-rama='" . (isset($rama) ? $rama : '') . "'
                    >
                        Editar
                    </button>

                    <button class='btn btn-danger btn-sm delete-user' data-id='{$user['id_usr']}'>
                        Eliminar
                    </button>
                </td>
            </tr>";
        }    
        echo $rows;
    }


    function sendRecoveryMail ($mailManager, $usr) {
        
        $token = Token::create([
            'user_id' => $usr[0]['id_usr'],
            'correo' => $usr[0]['correo'],
            'nombre' => $usr[0]['nombre'],
            'type' => 'recover'
        ]);

        $params = [
            'nombre' => $usr[0]['nombre'],
            'correo' => $usr[0]['correo'],
            'token' => $token
        ];

        $to = $usr[0]['correo'];
        //Recoger el template que necesito
        $template = Templates::getTemplate('recuperar_password', $params);
        //Unir el body con el footer
        $body = $template['body'] . Templates::getDefaultFooter();
        //Coger el subject del correo
        $subject = $template['subject'];
        //Unir y enviar todo al usuario
        return $mailManager->sendMail($to, $subject, $body, true);
    }
