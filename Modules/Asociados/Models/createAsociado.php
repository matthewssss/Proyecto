<?php

function create ($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'insertData') {
        try {
            $conn->beginTransaction();
    
            // First, insert into padres table
            $sql_padre = "INSERT INTO padres (nombre, apellidos, correo, tel, estado_civil, dni) 
                          VALUES (:nombre, :apellidos, :correo, :tel, :estado_civil, :dni)";
            
            // Encriptar valores primero
            $nombre_encrypted = encryptField($_POST['nombre_padre']);
            $apellidos_encrypted = encryptField($_POST['apellidos_padre']);
            $correo_encrypted = encryptField($_POST['correo_padre']);
            $tel_encrypted = encryptField($_POST['telefono_padre']);
            $estado_civil_encrypted = encryptField($_POST['estado_civil']);
            $dni_encrypted = encryptField($_POST['dni_padre']);
            
            $stmt_padre = $conn->prepare($sql_padre);
            $stmt_padre->bindParam(':nombre', $nombre_encrypted);
            $stmt_padre->bindParam(':apellidos', $apellidos_encrypted);
            $stmt_padre->bindParam(':correo', $correo_encrypted);
            $stmt_padre->bindParam(':tel', $tel_encrypted);
            $stmt_padre->bindParam(':estado_civil', $estado_civil_encrypted);
            $stmt_padre->bindParam(':dni', $dni_encrypted);
            
            $stmt_padre->execute();
            $id_padre = $conn->lastInsertId();
    
            // Then, insert into inscripcion_asociados
            $sql_inscripcion = "INSERT INTO inscripcion_asociados (
                nombre, apellidos, DNI, fecha_nacimiento, 
                edad, unidad, municipio, provincia, 
                comunidad_autonoma, id_usr
            ) VALUES (
                :nombre, :apellidos, :dni, :fecha_nacimiento,
                :edad, :unidad, :municipio, :provincia,
                :comunidad_autonoma, :id_usr
            )";
            
            // Encriptar valores del asociado
            $nombre_asoc_encrypted = encryptField($_POST['nombre']);
            $apellidos_asoc_encrypted = encryptField($_POST['apellidos']);
            $dni_asoc_encrypted = encryptField($_POST['dni']);
            $fecha_nac_encrypted = encryptField($_POST['fecha_nacimiento']);
            $edad_encrypted = encryptField($_POST['edad']);
            $unidad = $_POST['unidad'];
            //$cp_encrypted = encryptField($_POST['cp']);
            $municipio_encrypted = encryptField($_POST['municipio']);
            $provincia_encrypted = encryptField($_POST['provincia']);
            $comunidad_encrypted = encryptField($_POST['comunidad_autonoma']);
            $id = $_SESSION['user_id'] ;
            
            $stmt_inscripcion = $conn->prepare($sql_inscripcion);
            $stmt_inscripcion->bindParam(':nombre', $nombre_asoc_encrypted);
            $stmt_inscripcion->bindParam(':apellidos', $apellidos_asoc_encrypted);
            $stmt_inscripcion->bindParam(':dni', $dni_asoc_encrypted);
            $stmt_inscripcion->bindParam(':fecha_nacimiento', $fecha_nac_encrypted);
            $stmt_inscripcion->bindParam(':edad', $edad_encrypted);
            $stmt_inscripcion->bindParam(':unidad', $unidad);
            //$stmt_inscripcion->bindParam(':cp', $cp_encrypted);
            $stmt_inscripcion->bindParam(':municipio', $municipio_encrypted);
            $stmt_inscripcion->bindParam(':provincia', $provincia_encrypted);
            $stmt_inscripcion->bindParam(':comunidad_autonoma', $comunidad_encrypted);
            $stmt_inscripcion->bindParam(':id_usr', $id);
    
            $stmt_inscripcion->execute();
            $id_inscripcion = $conn->lastInsertId();
    
            // Finally, insert into padre_inscrito
            $sql_relacion = "INSERT INTO padre_inscrito (id_pad, id_ins) VALUES (:id_pad, :id_ins)";
            $stmt_relacion = $conn->prepare($sql_relacion);
            $stmt_relacion->bindParam(':id_pad', $id_padre);
            $stmt_relacion->bindParam(':id_ins', $id_inscripcion);
            $stmt_relacion->execute();
    
            // If everything is OK, commit the transaction
            $conn->commit();
            echo json_encode(['error' => false, 'message' => 'Registro completado correctamente']);
    
        } catch (Exception $e) {
            // If there's an error, rollback the transaction
            if ($conn) {
                $conn->rollBack();
            }
            echo json_encode(['error' => true, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        header("HTTP/1.0 404 Forbidden");
        include_once "../../../Public/errorPages/404.html";
        exit();
    }
}