<?php

    session_start();
    include_once "../../../Models/ConnectDatabase.php"; //Conexion a la bd;
    require_once '../../../Mail/MailManager.php'; //Gestor para enviar correos
    require_once '../../../Mail/Templates.php'; //Plantillas de los correos
    include_once "../../Usuarios/Models/Usuario.php"; // Clase Usuario
    include_once "../../Roles/Models/Rol.php"; // Clase Rol
    require_once '../../../Mail/MailManager.php'; //Gestor para enviar correos
    require_once '../../../Mail/Templates.php'; //Plantillas de los correos
    require_once '../Models/getDataCirculares.php';
    require_once '../Models/CreateFile.php';
    require_once '../Models/GetEventData.php';
    require_once '../Models/DeleteCircular.php';
    require_once '../Models/EnviarCircular.php';
    require_once '../Models/UpdateCircular.php';

    use Mailer\Templates; //Poder usar los templates
    // Crear el objeto de MailManager
    $mailManager = new MailManager();
    //Pilla la conexion del connect database
    $connect = $conexion;
    
    if (!isset($_GET['action']) && !isset($_POST['action'])) {
        header("HTTP/1.0 404 Forbidden");
        include_once "../../../Public/errorPages/404.html";
        exit();
    }

    $action = $_POST['action'] ?? $_GET['action'] ?? null;

    switch ($action) {
        case 'loadFiles':
            echo createCards($connect);
            break;
        case 'createCircular':
            echo createCircular($connect);
            break;
        case 'getEvents':
            echo getEventsData($connect);
            break;
        case 'getAllData':
            echo createDataTables($connect);
            break;
        case 'deleteFirstTime':
            echo deleteCircular($connect);
            break;
        case 'enviarCircular':
            echo enviarCircular($connect, $mailManager);
            break;
        case 'editCircular':
            echo updateCircular($connect);
            break;
         case 'getSession':
            echo json_encode(['exits' => isset($_SESSION["user"])]);
            break;
        default:
            echo json_encode(['error' => true, 'message' => 'Invalid action']);
            break;
    }


    function createCards($connect) {
        $circulares = getNotDeleteCirculares($connect);
    
        $recientesPorRama = [];
        $htmlRecientes = '';
        $htmlTodas = '';
    
        foreach ($circulares as $circular) {
            $rama = strtolower($circular['rama']);
    
            // Si aún no hemos agregado la más reciente de esta rama
            if (!isset($recientesPorRama[$rama])) {
                $recientesPorRama[$rama] = true;
                $htmlRecientes .= generarCardRama($circular);
            } else {
                $htmlTodas .= generarCardGeneral($circular);
            }
        }

        if (isset($_SESSION['rama'])) {
            $idRol = $_SESSION['rama']['idRol'];
            return json_encode([
                'recientes' => $htmlRecientes,
                'todas' => $htmlTodas,
                'idRol' => $idRol
            ]); 
        }
    
        return json_encode([
            'recientes' => $htmlRecientes,
            'todas' => $htmlTodas
        ]);
    }

    function generarCardRama($circular) {
        $rama = strtolower($circular['rama']);
        $titulo = htmlspecialchars($circular['titulo']);
        $inicio = $circular['fecha_inicio_actividad'];
        $fin = $circular['fecha_fin_actividad'];
        $pernoctas = $circular['pernoctas'];
        $path = $circular['archivo_path'];
        $ubicacion = $circular['ubicacion']; // Campo de ubicación
    
        // SVG de getwaves.io: asegurar que se renderice correctamente
        $colorRama = '';
        if ($circular['rama'] == 'Lobatos') {
            $colorRama = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="#ffd700" fill-opacity="0.8" d="M0,288L16,261.3C32,235,64,181,96,165.3C128,149,160,171,192,197.3C224,224,256,256,288,261.3C320,267,352,245,384,224C416,203,448,181,480,181.3C512,181,544,203,576,181.3C608,160,640,96,672,80C704,64,736,96,768,122.7C800,149,832,171,864,176C896,181,928,171,960,160C992,149,1024,139,1056,133.3C1088,128,1120,128,1152,149.3C1184,171,1216,213,1248,197.3C1280,181,1312,107,1344,101.3C1376,96,1408,160,1424,192L1440,224L1440,0L1424,0C1408,0,1376,0,1344,0C1312,0,1280,0,1248,0C1216,0,1184,0,1152,0C1120,0,1088,0,1056,0C1024,0,992,0,960,0C928,0,896,0,864,0C832,0,800,0,768,0C736,0,704,0,672,0C640,0,608,0,576,0C544,0,512,0,480,0C448,0,416,0,384,0C352,0,320,0,288,0C256,0,224,0,192,0C160,0,128,0,96,0C64,0,32,0,16,0L0,0Z"></path></svg>';
        } else if ($circular['rama'] == 'Exploradores') {
            $colorRama = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="#0099ff" fill-opacity="0.8" d="M0,0L16,53.3C32,107,64,213,96,256C128,299,160,277,192,256C224,235,256,213,288,218.7C320,224,352,256,384,240C416,224,448,160,480,160C512,160,544,224,576,213.3C608,203,640,117,672,85.3C704,53,736,75,768,112C800,149,832,203,864,224C896,245,928,235,960,202.7C992,171,1024,117,1056,85.3C1088,53,1120,43,1152,58.7C1184,75,1216,117,1248,154.7C1280,192,1312,224,1344,224C1376,224,1408,192,1424,176L1440,160L1440,0L1424,0C1408,0,1376,0,1344,0C1312,0,1280,0,1248,0C1216,0,1184,0,1152,0C1120,0,1088,0,1056,0C1024,0,992,0,960,0C928,0,896,0,864,0C832,0,800,0,768,0C736,0,704,0,672,0C640,0,608,0,576,0C544,0,512,0,480,0C448,0,416,0,384,0C352,0,320,0,288,0C256,0,224,0,192,0C160,0,128,0,96,0C64,0,32,0,16,0L0,0Z"></path></svg>';
        } else {
            $colorRama = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="#fc0000" fill-opacity="0.9" d="M0,288L16,288C32,288,64,288,96,256C128,224,160,160,192,144C224,128,256,160,288,186.7C320,213,352,235,384,224C416,213,448,171,480,128C512,85,544,43,576,48C608,53,640,107,672,149.3C704,192,736,224,768,218.7C800,213,832,171,864,154.7C896,139,928,149,960,138.7C992,128,1024,96,1056,74.7C1088,53,1120,43,1152,80C1184,117,1216,203,1248,234.7C1280,267,1312,245,1344,213.3C1376,181,1408,139,1424,117.3L1440,96L1440,0L1424,0C1408,0,1376,0,1344,0C1312,0,1280,0,1248,0C1216,0,1184,0,1152,0C1120,0,1088,0,1056,0C1024,0,992,0,960,0C928,0,896,0,864,0C832,0,800,0,768,0C736,0,704,0,672,0C640,0,608,0,576,0C544,0,512,0,480,0C448,0,416,0,384,0C352,0,320,0,288,0C256,0,224,0,192,0C160,0,128,0,96,0C64,0,32,0,16,0L0,0Z"></path></svg>';
        }
    
        // Verificamos si pernoctas es mayor que 0
        $pernoctasTexto = ($pernoctas > 0) ? "<span><strong>Pernoctas:</strong> $pernoctas días</span>" : "";

        return <<<HTML
        <div class="col-lg-4 col-sm-12">
            <div class="info card-circular $rama">
            <span class="iconify novedad-icon" data-icon="mdi:star-circle" title="Novedad"></span>

                <h2>{$circular['rama']}</h2>
                <h4>$titulo</h4>
                <div id="recentData">
                    <span><strong>Fecha inicio:</strong> $inicio</span>
                    <span><strong>Fecha fin:</strong> $fin</span>
                    $pernoctasTexto
                    <span><strong>Lugar:</strong> $ubicacion</span>
                </div>
                <div class="card-footer text-center">
                    <!-- Botones: Ver, Descargar, Compartir -->
                    <div class="d-flex justify-content-between">
                        <!-- Botón de Ver (Ojo) -->
                        <a href="$path" target="_blank" class="btn btn-outline-primary w-33">
                            <span class="iconify" data-icon="majesticons:open"></span>
                        </a>
                        <!-- Botón de Descargar (Flecha) -->
                        <a href="$path" download class="btn btn-outline-primary w-33">
                            <span class="iconify" data-icon="ic:baseline-download"></span>
                        </a>
                        <!-- Botón de Compartir (Icono de compartir) -->
                        <a href="javascript:void(0)" class="btn btn-outline-primary w-33 shareBtnClass" id="shareBtn" data-path="$path">
                            <span class="iconify" data-icon="mdi:share-variant"></span>
                        </a>
                    </div>
                </div>
                <div class="colorBar $circular[rama] mt-2"></div>
            </div>
        </div>
        HTML;

    }
    
    function generarCardGeneral($circular) {
        $titulo = htmlspecialchars($circular['titulo']);
        $inicio = $circular['fecha_inicio_actividad'];
        $fin = $circular['fecha_fin_actividad'];

        $hoy = date('Y-m-d');
        if ($hoy < $inicio) {
            $textoInicio = "$inicio";
        } elseif ($hoy >= $inicio && $hoy <= $fin) {
            $textoInicio = "En curso...⌛";
        } else {
            $textoInicio = "Realizada ✔";
        }
        $rama = ucfirst(strtolower($circular['rama']));
        $path = htmlspecialchars($circular['archivo_path']);
        $pernoctas = (int)$circular['pernoctas'];
        $ubicacion = htmlspecialchars($circular['ubicacion']);
        $mes = date('n', strtotime($inicio)); // 1-12 sin cero
        $fechaOrden = date('Ymd', strtotime($inicio)); // para ordenar
    
        $pernoctasTexto = $pernoctas > 0 ? "<p class='mb-1'><strong>Pernoctas:</strong> $pernoctas día(s)</p>" : "";
        $emoji = getRandomScoutEmoji(); // Llamada a la función para obtener un emoji aleatorio
        $colorRama = getRamaColor($rama); // Función para obtener el color de la rama (deberás crear esta función)
    
        // Estructura de la card
        return <<<HTML
            <div class="col-md-4 mb-4 circular" 
                data-rama="$rama" 
                data-mes="$mes" 
                data-fecha="$fechaOrden" 
                data-pernoctas="$pernoctas"
                data-lugar="$ubicacion"
                data-titulo="$titulo">
                <div class="card h-100 shadow-sm">
                    <div class="card-header position-relative bg-light">
                        <br>
                        <span class="badge bg-dark position-absolute top-0 end-0 m-2 fechainicio">$textoInicio</span>
                        <div class="d-flex justify-content-center align-items-center icono">
                            <span class="iconify text-gray-300 me-3 text-3xl" data-icon="$emoji"></span>
                        </div>
                        <!-- Línea bajo el emoji que cambia de color según la rama -->
                        <div class="rama-line" style="border-top: 5px solid $colorRama; margin-top: 10px;"></div>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">$titulo</h5>
                        <p class="mb-1"><strong>Fin:</strong> $fin</p>
                        $pernoctasTexto
                        <p class="mb-1"><strong>Ubicación:</strong> $ubicacion</p>
                        <p class="mb-0"><span class="badge bg-info text-dark">$rama</span></p>
                    </div>
                    <div class="card-footer text-center">
                        <!-- Botones: Ver, Descargar, Compartir -->
                        <div class="d-flex justify-content-between">
                            <!-- Botón de Ver (Ojo) -->
                            <a href="$path" target="_blank" class="btn btn-outline-primary w-33">
                                <span class="iconify" data-icon="majesticons:open"></span>
                            </a>
                            <!-- Botón de Descargar (Flecha) -->
                            <a href="$path" download class="btn btn-outline-primary w-33">
                                <span class="iconify" data-icon="ic:baseline-download"></span>
                            </a>
                            <!-- Botón de Compartir (Icono de compartir) -->
                            <a href="javascript:void(0)" class="btn btn-outline-primary w-33 shareBtnClass" id="shareBtn" data-path="$path">
                                <span class="iconify" data-icon="mdi:share-variant"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        HTML;
    }
    // Función para obtener el color de la rama
    function getRamaColor($rama) {
        $colors = [
            'lobatos' => 'yellow',   
            'exploradores' => 'blue', 
            'pioneros' => 'red',
        ];
        return isset($colors[strtolower($rama)]) ? $colors[strtolower($rama)] : '#000000'; // Color por defecto negro
    }
    
    function getRandomScoutEmoji() {
        $emojis = [
            'mdi:pine-tree',         // Árbol
            'mdi:tree',              // Árbol grande
            'mdi:campfire',          // Fuego de campamento
            'mdi:tent',              // Tienda de campaña
            'mdi:mountain',          // Montaña
            'mdi:compass',           // Brújula
            'mdi:binoculars',        // Prismáticos
            'mdi:map',               // Mapa
            'mdi:leaf',              // Hoja
            'mdi:flower',            // Flor
            'mdi:weather-sunny',     // Sol
            'mdi:weather-night',     // Luna (excursiones nocturnas)
            'mdi:cloud',             // Nubes
            'mdi:water',             // Ríos, lagos
            'mdi:bee',               // Abeja (naturaleza)
            'mdi:telescope',         // Telescopio (exploración)
            'mdi:earth',             // Planeta Tierra
            'mdi:foot-print',        // Huellas de pies (senderismo)
            'mdi:dog',               // Perro (naturaleza y excursiones)
            'mdi:owl',               // Búho (aventura de noche)
            'mdi:weather-sunset',    // Puesta de sol
            'mdi:run-fast',          // Corriendo (aventura)
            'mdi:tree-outline',      // Árbol outline
            'mdi:pine-tree-variant', // Variación de pinos
        ];
        return $emojis[array_rand($emojis)];
    }

    function createDataTables ($connect) {
        $data = getDataCirculares($connect);
        $no_enviados = '';
        $enviados = '';
        $eliminados = '';

        if (!empty($data)) {
            foreach ($data as $fila) {
                // Obtener nombre del usuario creador
                $nombreCreador = trim($fila['creador_nombre'] . ' ' . $fila['creador_apellidos']);
                if (empty($nombreCreador)) {
                    $nombreCreador = 'Usuario no encontrado';
                }
                
                $nombreEliminador = '';
                if ($fila['eliminado'] == 1) {
                    $nombreEliminador = trim($fila['eliminador_nombre'] . ' ' . $fila['eliminador_apellidos']);
                    if (empty($nombreEliminador)) {
                        $nombreEliminador = 'Usuario no encontrado';
                    }
                }
                $fechasActividad = $fila['pernoctas'] == 0 ? htmlspecialchars($fila['fecha_inicio_actividad']) : htmlspecialchars($fila['fecha_inicio_actividad']) . ' - ' . htmlspecialchars($fila['fecha_fin_actividad']);                

                $tr = '<tr 
                            data-id="'.$fila['id_file'].'"
                            data-titulo="'.htmlspecialchars($fila['titulo']).'"
                            data-path="'.htmlspecialchars($fila['archivo_path']).'"
                            data-pernoctas="'.$fila['pernoctas'].'"
                            data-fecha-inicio="'.$fila['fecha_inicio_actividad'].'"
                            data-fecha-fin="'.$fila['fecha_fin_actividad'].'"
                            data-rama="'.$fila['rama'].'"
                            data-ubicacion="'.$fila['ubicacion'].'"
                        >';
                $tr .= '<td>' . htmlspecialchars($fila['titulo']) . '</td>';
                $tr .= '<td>' . $fechasActividad . '</td>';
                $tr .= '<td>' . htmlspecialchars($fila['pernoctas']) . '</td>';
                $tr .= '<td>' . htmlspecialchars($fila['rama']) . '</td>';
                $tr .= '<td>' . htmlspecialchars($nombreCreador) . '</td>';
                
                if ($fila['eliminado'] == 1) {
                    $tr .= '<td>' . htmlspecialchars($fila['fecha_subida']) . '</td>';
                    $tr .= '<td>' . htmlspecialchars($nombreEliminador) . '</td>';
                    $tr .= '<td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-primary dropdown-toggle btnPrincipal" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="iconify " data-icon="hugeicons:more-02" style="font-size: 20px;"></span> Más acciones
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li><button class="dropdown-item btnAction btn-ver"><span class="iconify" data-icon="majesticons:open" style="font-size: 20px;"></span> Ver</button></li>
                                        <li><button class="dropdown-item btnAction btn-clonar"><span class="iconify" data-icon="clarity:clone-solid" style="font-size: 20px;"></span> Clonar</button></li>
                                        <li><button class="dropdown-item btnAction btn-descargar"><span class="iconify" data-icon="ic:baseline-download" style="font-size: 20px;"></span> Descargar</button></li>
                                    </ul>
                                </div>
                            </td>';
                    $eliminados .= $tr . '</tr>';
                } elseif ($fila['enviado'] == 1) {
                    $tr .= '<td>' . htmlspecialchars($fila['fecha_subida']) . '</td>';
                    $tr .= '<td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-primary dropdown-toggle btnPrincipal" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="iconify " data-icon="hugeicons:more-02" style="font-size: 20px;"></span> Más acciones
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li><button class="dropdown-item btnAction btn-borrar"><span class="iconify" data-icon="icomoon-free:bin" style="font-size: 20px;"></span> Borrar</button></li>
                                        <li><button class="dropdown-item btnAction btn-ver"><span class="iconify" data-icon="majesticons:open" style="font-size: 20px;"></span> Ver</button></li>
                                        <li><button class="dropdown-item btnAction btn-clonar"><span class="iconify" data-icon="clarity:clone-solid" style="font-size: 20px;"></span> Clonar</button></li>
                                        <li><button class="dropdown-item btnAction btn-descargar"><span class="iconify" data-icon="ic:baseline-download" style="font-size: 20px;"></span> Descargar</button></li>
                                    </ul>
                                </div>
                            </td>';
                    $enviados .= $tr . '</tr>';
                } else {
                    $tr .= '<td>
                                <div class="d-flex justify-content-start gap-1">
                                    <!-- Botón Enviar fuera del dropdown -->
                                    <button class="btn btn-sm btn-success btn-enviar btnPrincipal">
                                        <span class="iconify" data-icon="material-symbols:send" style="font-size: 20px;"></span> Enviar
                                    </button>
                                    
                                    <!-- Dropdown con las demás acciones -->
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-primary dropdown-toggle btnPrincipal" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="iconify " data-icon="hugeicons:more-02" style="font-size: 20px;"></span> Más acciones
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <li><button class="dropdown-item btnAction btn-editar"><span class="iconify" data-icon="iconamoon:edit" style="font-size: 20px;"></span> Editar</button></li>
                                            <li><button class="dropdown-item btnAction btn-borrar"><span class="iconify" data-icon="icomoon-free:bin" style="font-size: 20px;"></span> Borrar</button></li>
                                            <li><button class="dropdown-item btnAction btn-ver"><span class="iconify" data-icon="majesticons:open" style="font-size: 20px;"></span> Ver</button></li>
                                            <li><button class="dropdown-item btnAction btn-clonar"><span class="iconify" data-icon="clarity:clone-solid" style="font-size: 20px;"></span> Clonar</button></li>
                                            <li><button class="dropdown-item btnAction btn-descargar"><span class="iconify" data-icon="ic:baseline-download" style="font-size: 20px;"></span> Descargar</button></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>';
                    $no_enviados .= $tr . '</tr>';
                }
                
            }
        }

        return json_encode([
            'success' => true,
            'no_enviados' => $no_enviados,
            'enviados' => $enviados,
            'eliminados' => $eliminados
        ]);
    }