<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../Usuarios/Models/Usuario.php';
require_once '../../Padres/Models/Padre.php';
include_once '../../Roles/Models/Rol.php';
require_once 'Asociado.php';

function getData($connect) {
    if (isset($_SESSION['user_id'])) {
        $idUsr = $_SESSION['user_id'];
        
        $query = "
            SELECT 
                i.*, 
                p.id_pad, p.nombre AS nombre_padre, p.apellidos AS apellidos_padre, 
                p.correo AS correo_padre, p.tel AS telefono_padre, 
                p.estado_civil, p.dni AS dni_padre
            FROM inscripcion_asociados i
            LEFT JOIN padre_inscrito pi ON i.id_ins = pi.id_ins
            LEFT JOIN padres p ON pi.id_pad = p.id_pad 
            WHERE i.eliminado = 0 AND p.eliminado = 0
        ";
        $rama = null;
        $idRol = null;
        if (isset($_SESSION['rama'])) {
            $idRol = $_SESSION['rama']['idRol'];
            $rama = $_SESSION['rama']['rama'];
        } else {
            $usr = unserialize($_SESSION['user']);
            $idRol = $usr->getIdRol();
        }

        if ($idRol == 1) {
            $query .= "AND i.id_usr = :id";
        }
        else if ($idRol >= 2 && $idRol <= 4) {
            $query .= "AND  i.id_usr = :id OR i.unidad = :unidad";
        } 

        $results = $connect->prepare($query);
        if ($idRol == 1) {
            $results->bindParam(':id', $idUsr);
        } else if ($idRol >= 2 && $idRol <= 4) {
            $results->bindParam(':id', $idUsr);
            $results->bindParam(':unidad', $rama);
        }

        $results->execute();
        if ($results->rowCount() === 0) {
            $noData = '
            <div class="container text-center mt-5">
                <div class="alert alert-warning" role="alert">
                    <h4 class="alert-heading">No hay datos disponibles</h4>
                    <p>Actualmente no se encuentran datos para mostrar. Si se trata de un error ponte en contacto con el administrador</p>
                    <hr>
                    <p class="mb-0 text-muted">
                        <small>¿Aún no tienes datos? <a href="#" data-bs-toggle="modal" data-bs-target="#exampleModal">Registra ahora a tu hijo/a</a></small>
                    </p>
                </div>
            </div>';
            echo json_encode(['error' => false, 'html' => $noData]);
            return;
        }
        
        

        $datos = $results->fetchAll(PDO::FETCH_ASSOC);
        
        $asociadosObjetos = [];  // Clases
        $asociadosData = [];     // Cards data
        $cardsHtml = '';

        // Consultas únicas para obtener todos los nombres
        $comunidades = $connect->query("SELECT idCCAA, Nombre FROM CCAA")->fetchAll(PDO::FETCH_KEY_PAIR);
        $provincias = $connect->query("SELECT idProvincia, Provincia FROM PROVINCIAS")->fetchAll(PDO::FETCH_KEY_PAIR);
        $municipios = $connect->query("SELECT idMunicipio, Municipio FROM MUNICIPIOS")->fetchAll(PDO::FETCH_KEY_PAIR);



        foreach ($datos as $dato) {
            // Create and store the object
            $asociado = new Asociado($dato);
             $asociado->mapLocationNames($comunidades, $provincias, $municipios);
            $asociadosObjetos[] = $asociado;
            $cardsHtml .= createCard($asociado, $rama, $idRol);
        }

        // Store objects in session
        $_SESSION['asociados'] = serialize($asociadosObjetos);

        // Return display data for cards
        echo json_encode(['error' => false, 'html' => $cardsHtml]);
    } else {
        echo json_encode(['error' => true, 'msg' => 'No se encontró una sesion']);
    }
}


function createCard($asociado, $ramaUsr, $idRol) {
    // Usamos los getters para obtener los datos desencriptados
    $idIns = $asociado->getIdIns();
    $nombre = $asociado->getNombre();
    $apellidos = $asociado->getApellidos();
    $dni = $asociado->getDNI();
    $fechaNacimiento = $asociado->getFechaNacimiento();
    $edad = $asociado->getEdad();
    $unidad = $asociado->getUnidad();

    $idPadre = $asociado->getPadreId();
    $nombrePadre = $asociado->getPadreNombre();
    $apellidosPadre = $asociado->getPadreApellidos();
    $dniPadre = $asociado->getPadreDni();
    $correoPadre = $asociado->getPadreCorreo();
    $telefonoPadre = $asociado->getPadreTel();
    $estadoCivil = $asociado->getPadreEstadoCivil();

    // Determinamos la clase de la unidad, por ejemplo 'rutas'
    $unidadClase = strtolower($unidad); 

    // Determinamos si el asociado es del usuario actual (si es del usuario, agregamos 'mios')
    $esDelUsuario = (isset($_SESSION['user_id']) && $asociado->getIdUsr() == $_SESSION['user_id']) ? 'mios' : '';
    $unidadCard = '';
    if ($ramaUsr != null) {
        $unidadCard = ($unidad == $ramaUsr) ? 'unidad' : '';
    }

    // Combinamos ambas clases, la de la unidad y la del usuario si es necesario
    // Así, siempre vamos a tener las dos clases: 'mios' o 'unidad' y la unidad (por ejemplo 'rutas')
    $filtro = "mix todos $unidadCard $esDelUsuario";
    $orden = 'data-nombre="' . $nombre . '" '
           . 'data-apellidos="' . $apellidos . '" '
           . 'data-edad="' . $edad . '" '
           . 'data-unidad="' . $unidad . '" '
           . 'data-padre-nombre="' . $nombrePadre . '" '
           . 'data-padre-apellidos="' . $apellidosPadre . '" '
           . 'data-padre-correo="' . $correoPadre . '" '
           . 'data-padre-telefono="' . $telefonoPadre . '"';
    

    // Comenzamos el HTML de la tarjeta
    $html = '<div class="card mx-auto ' . $filtro . '" style="max-width: 300px; position: relative;" ' . $orden . '>';
    $html .= '<div class="side-bar ' . $unidadClase . '"></div>';
    //<div class="mb-3"><strong>DNI:</strong> ' . $dni . '</div>
    //<div class="mb-3"><strong>DNI:</strong> ' . $dniPadre . '</div>
    // Cuerpo de la tarjeta
    $html .= '<div class="card-body">
            <div class="front">
                <h2 class="card-title">Datos del Niño</h2>
                <div class="mb-3"><strong>Nombre:</strong> ' . $nombre . '</div>
                <div class="mb-3"><strong>Apellidos:</strong> ' . $apellidos . '</div>
                <div class="mb-3"><strong>Fecha de Nacimiento:</strong> ' . $fechaNacimiento . '</div>
                <div class="mb-3"><strong>Edad:</strong> ' . $edad . ' años</div>
                <div class="mb-3"><strong>Rama:</strong> ' . $unidad . '</div>

                <div class="botones">
                    <button class="btn btn-link detallesBtn" data-id="' . $idIns . '">Detalles</button>
                    <button class="btn btn-link rotate">Ver familia</button>';
                    
                    // Mostrar botones de Editar y Eliminar solo si $idRol no es 2 ni 3
                    if ($idRol != 2 && $idRol != 3) {
                        $html .= '
                        <button class="btn btn-link editData" data-id="' . $idIns . '">Editar</button>
                        <button class="btn btn-danger deleteData" data-id="' . $idIns . '" data-idP="' . $idPadre . '">Eliminar</button>';
                    }
                        
    $html .= '
                </div>
            </div>

            <div class="back d-none">
                <h2 class="card-title">Datos del Padre</h2>
                <div class="mb-3"><strong>Nombre:</strong> ' . $nombrePadre . '</div>
                <div class="mb-3"><strong>Apellidos:</strong> ' . $apellidosPadre . '</div>
                <div class="mb-3"><strong>Correo:</strong> ' . $correoPadre . '</div>
                <div class="mb-3"><strong>Teléfono:</strong> ' . $telefonoPadre . '</div>
                <div class="mb-3"><strong>Estado Civil:</strong> ' . $estadoCivil . '</div>

                <div class="botones">
                    <button class="btn btn-link detallesBtn" data-id="' . $idIns . '">Detalles</button>
                    <button class="btn btn-link rotate">Ver niño</button>';
                    
                    // Mostrar botones de Editar y Eliminar solo si $idRol no es 2 ni 3
                    if ($idRol != 2 && $idRol != 3) {
                        $html .= '
                        <button class="btn btn-link editData" data-id="' . $idIns . '">Editar</button>
                        <button class="btn btn-danger deleteData" data-id="' . $idIns . '" data-idP="' . $idPadre . '">Eliminar</button>';
                    }
                    
    $html .= '
                </div>
            </div>
        </div>';

    $html .= '</div>'; // Cierre de card
    return $html;
}
