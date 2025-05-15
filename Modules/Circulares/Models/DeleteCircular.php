<?php
    function deleteCircular($conn) {
        $idCircular = $_POST['id'];
        $idUsuario = $_SESSION['user_id'];
        
        // Actualizamos el estado de la circular a eliminada y guardamos el ID del usuario que la eliminó
        $sql = "UPDATE circulares SET eliminado = 1, eliminado_por = :idUsuario WHERE id_file = :idCircular";
        $stmt = $conn->prepare($sql);
        
        // Vinculamos los parámetros
        $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $stmt->bindParam(':idCircular', $idCircular, PDO::PARAM_INT);
        
        // Ejecutamos la consulta
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }