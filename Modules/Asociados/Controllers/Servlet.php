<?php
    
    session_start();
    include_once "../../../Models/ConnectDatabase.php"; //Conexion a la bd;
    include_once "../Models/getAsociado.php";
    include_once "../Models/loadAddresses.php";
    include_once "../Models/createAsociado.php";
    require_once '../../Usuarios/Models/Usuario.php';
    require_once '../../Roles/Models/Rol.php'; // Clase Rol
    require_once '../Models/Asociado.php';
    require_once '../Models/updateData.php';
    require_once '../Models/deleteAsociado.php';


    if (!isset($_GET['action']) && !isset($_POST['action'])) {
        header("HTTP/1.0 404 Forbidden");
        include_once "../../../Public/errorPages/404.html";
        exit();
    }

    
    $connect = $conexion;
           
    $action = $_POST['action'] ?? $_GET['action'] ?? null;

    switch ($action) {
        case 'session':
            echo json_encode(['exits' => isset($_SESSION["user"])]);
            break;
        case 'data':
            echo getData($connect);
            break;
        case 'show':
            $asociados = unserialize($_SESSION['asociados']);
            $datosAsociados = [];
            
            foreach ($asociados as $asociado) {
                $datosAsociados[] = $asociado->getAllData();
            }
            
            echo json_encode([
                'error' => false,
                'msg' => $datosAsociados
            ]);
            break;
        case 'getComunidades':
            echo getComunidades($connect);
            break;
        case 'getProvincias':
            echo getProvincias($connect, $_GET['comunidadId']);
            break;
        case 'getMunicipios':
            echo getMunicipios($connect, $_GET['provinciaId']);
            break;
        case 'getDetallesAsociado':
            echo traerDatos();
            break;
        case 'insertData':
            echo create($connect);
            break;
        case 'getCampo':
            echo getCampo($_GET['campo']);
            break;
        case 'getCampoUsr':
            echo getCampoUsr($_GET['campo']);
            break;
        case 'updateAsociadoYPadre':
            echo updateData($connect);
            break;
        case 'deleteAsociado':
            echo deleteAsociado($connect);
            break;
        default:
            echo json_encode(['error' => true, 'message' => 'Invalid action']);
            break;
    }

    function traerDatos () {
        $asociados = unserialize($_SESSION['asociados']);
        $id = $_GET['idAsociado'];
        $datosAsociados = null;

        foreach ($asociados as $asociado) {
            if ($asociado->getIdIns() == $id) {
                $datosAsociados = $asociado->getAllData();
                break;
            }
        }

        if ($datosAsociados) {
            return json_encode([
                'error' => false,
                'msg' => $datosAsociados
            ]);
        } else {
            return json_encode([
                'error' => true,
                'msg' => 'Asociado no encontrado'
            ]);
        }
    }

    function getCampo ($campo) {
        $idAsociado = $_GET['idAsociado'];
        $asociado = getAsociado($idAsociado);

        switch ($campo) {
            case 'nombreCompleto':
                return $asociado->getNombre() . ' ' . $asociado->getApellidos();
            case 'nombre':
                return $asociado->getNombre();
            case 'apellidos':
                return $asociado->getApellidos();
            case 'DNI':
                return $asociado->getDNI();
            case 'fecha_nacimiento':
                return $asociado->getFechaNacimiento();
            case 'edad':
                return $asociado->getEdad();
            case 'unidad':
                return $asociado->getUnidad();
            case 'cp':
                return $asociado->getCp();
            case 'municipioId':
                return $asociado->getMunicipioId();
            case 'provinciaId':
                return $asociado->getProvinciaId();
            case 'comunidad_autonomaId':
                return $asociado->getComunidadAutonomaId();
            default:
                return null;
        }
    }

    function getCampoUsr ($campo) {
        $usr = unserialize($_SESSION['user']);

        switch ($campo) {
            case 'nombre':
                return $usr->getNombre();
            case 'apellidos':
                return $usr->getApellidos();
            case 'DNI':
                return $usr->getDNI();
            case 'fecha_nacimiento':
                return $usr->getFechaNacimiento();
            case 'edad':
                return $usr->getEdad();
            case 'unidad':
                return $usr->getUnidad();
            case 'cp':
                return $usr->getCp();
            case 'municipio':
                return $usr->getMunicipioName();
            case 'provincia':
                return $usr->getProvinciaName();
            case 'comunidad_autonoma':
                return $usr->getComunidadAutonomaName();
            case 'idRol':
                return $usr->getIdRol();
            case 'idUsr':
                return $usr->getIdUsr();
            default:
                return null;
        }
    }

    function getAsociado($idAsociado) {
        $asociados = unserialize($_SESSION['asociados']);
        $id = $_GET['idAsociado'];
        $datosAsociados = null;

        foreach ($asociados as $asociado) {
            if ($asociado->getIdIns() == $id) {
                return $asociado;
            }
        }
    }


    function encryptField($field) {
        // Recoge la clave de cifrado desde el archivo .env (variable de entorno)
        $encryption_key = getenv('ENCRYPTION_KEY'); // Clave secreta almacenada en el entorno del servidor
        
        // Genera un IV (vector de inicialización) aleatorio y seguro de longitud correcta para AES-256-CBC
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc')); 
        
        // Cifra el texto utilizando AES-256-CBC con la clave y IV generados
        $encrypted = openssl_encrypt($field, 'aes-256-cbc', $encryption_key, 0, $iv);
        
        // Si la encriptación falla, se lanza una excepción
        if ($encrypted === false) {
            throw new Exception("Encryption failed");
        }
        
        // Combina el IV y el texto cifrado, y luego lo codifica en Base64 para almacenamiento o transmisión segura
        return base64_encode($iv . $encrypted);
    }