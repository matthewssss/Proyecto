<?php 

function deleteUser($connect) {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        return ['success' => false, 'message' => 'ID de usuario no proporcionado.'];
    }

    try {
        $stmt = $connect->prepare("UPDATE usuarios SET eliminado = 1 WHERE id_usr = :id");
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Usuario desactivado correctamente.'];
        } else {
            return ['success' => false, 'message' => 'No se pudo desactivar el usuario.'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

