<?php 
use Mailer\Templates;

function enviarCircular($conn, $mailManager) {
    if (!isset($_POST['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'ID no recibido']);
        return;
    }

    $id = $_POST['id'];

    // Actualizar a enviado = 1
    $updateSql = "UPDATE circulares SET enviado = 1 WHERE id_file = :id";
    $stmtUpdate = $conn->prepare($updateSql);
    $stmtUpdate->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtUpdate->execute();

    // Obtener datos de la circular
    $selectSql = "SELECT titulo, fecha_inicio_actividad, fecha_fin_actividad, pernoctas, rama, ubicacion, archivo_path FROM circulares WHERE id_file = :id";
    $stmtSelect = $conn->prepare($selectSql);
    $stmtSelect->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtSelect->execute();
    $circular = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if (!$circular) {
        echo json_encode(['status' => 'error', 'message' => 'Circular no encontrada']);
        return;
    }

    $rama = $circular['rama'];

    // Buscar usuarios según la rama
    if ($rama == 'General') {
        // Si la rama es 'General', buscamos:
        // - Usuarios con rol 1 que están inscritos en alguna unidad (sin importar cuál).
        // - Usuarios con rol 5 (admin), sin importar su unidad.
        $sqlUsuarios = "
            SELECT u.id_usr, u.correo, u.nombre, u.id_rol 
            FROM usuarios u
            INNER JOIN inscripcion_asociados ia ON u.id_usr = ia.id_usr
            WHERE u.id_rol = 1 AND ia.unidad IS NOT NULL
            
            UNION
            
            SELECT u.id_usr, u.correo, u.nombre, u.id_rol
            FROM usuarios u
            LEFT JOIN monitores m ON u.id_usr = m.id_usr
            WHERE u.id_rol = 5
        ";
        $stmtUsuarios = $conn->prepare($sqlUsuarios);
    } else {
        // Si la rama es específica, usamos el valor de :rama
        $users = "
            SELECT u.id_usr, u.correo, u.nombre, u.id_rol 
            FROM usuarios u
            INNER JOIN inscripcion_asociados ia ON u.id_usr = ia.id_usr
            WHERE u.id_rol = 1 AND ia.unidad = :rama  -- Usuarios con rol 1 y unidad igual a la rama especificada
            
            UNION
            
            SELECT u.id_usr, u.correo, u.nombre, u.id_rol
            FROM usuarios u
            LEFT JOIN monitores m ON u.id_usr = m.id_usr
            WHERE (u.id_rol = 4 AND m.rama = :rama)  -- Monitores con rol 4 y rama igual a la rama especificada
            OR (u.id_rol = 5)                         -- Usuarios con rol 5 (admin)
        ";
        $stmtUsuarios = $conn->prepare($users);
        $stmtUsuarios->bindParam(':rama', $rama, PDO::PARAM_STR);
    }
    
    // Ejecutar la consulta
    $stmtUsuarios->execute();
    $users = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
    

    // ✅ Ahora enviamos correos directo, no usar session
    enviarCorreosCirculares($mailManager, $circular, $users);

    echo json_encode(['status' => 'success', 'message' => 'Circular enviada y correos disparados']);
}


function enviarCorreosCirculares($mailManager, $circular, $users) {
    $linkPublico = $circular['archivo_path']; // YA es URL directa
    $urlArchivo = obtenerRutaLocal($linkPublico);  // Misma URL para descargarlo
    
    // Descargar temporalmente el archivo
    $tmpFile = tempnam(sys_get_temp_dir(), 'circular_') . '.pdf';
    file_put_contents($tmpFile, file_get_contents($urlArchivo));
    
    foreach ($users as $user) {
        $mailManager = new MailManager();
        $params = [
            'nombre' => $user['nombre'],
            'titulo' => $circular['titulo'],
            'fecha_inicio' => $circular['fecha_inicio_actividad'],
            'fecha_fin' => $circular['fecha_fin_actividad'],
            'pernoctas' => $circular['pernoctas'],
            'ubicacion' => $circular['ubicacion'],
            'rama' =>  $circular['rama'],
            'link' => $linkPublico
        ];
        
        // Elegir template según el rol
        if ($user['id_rol'] == 1) {
            $template = Templates::getTemplate('notificar_padre', $params);
        } else {
            $template = Templates::getTemplate('notificar_admin', $params);
        }

        $body = $template['body'] . Templates::getDefaultFooter();
        $subject = $template['subject'];

        // Enviar el correo con el archivo adjunto
        $mailManager->sendMail($user['correo'], $subject, $body, true, $tmpFile);
    }
    
    // Borramos el archivo temporal después
    unlink($tmpFile);
} 


function obtenerRutaLocal($url) {
    // Definir la URL base que quieres reemplazar
    $urlBase = 'https://gruposcout.online/';

    // Verificar si la URL comienza con la URL base
    if (strpos($url, $urlBase) === 0) {
        // Reemplazar la parte pública de la URL con la ruta del servidor
        $rutaLocal = '/var/www/gruposcout.online/public_html/' . substr($url, strlen($urlBase));
        return $rutaLocal;
    } else {
        // Si la URL no comienza con la base, devolverla tal cual
        return $url;
    }
}

