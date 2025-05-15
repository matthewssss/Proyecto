<?php

function traerDatos($connect) {
    try {
        $id = $_SESSION['user_id'];
        $sql = "SELECT * FROM usuarios WHERE id_usr = :id";
        $stmt = $connect->prepare($sql);
        $stmt->execute(['id' => $id]);
        $newData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$newData) return false; // Si no hay datos, salir

        $user = new Usuario($newData);
        $rolInfo = sacarInfoRolChanges($newData['id_rol'], $connect);
        $user->setRol($rolInfo);

        $_SESSION['user'] = serialize($user);

        return true;
    } catch (Exception $e) {
        return false;
    }
}


function sacarInfoRolChanges($idRol, $connect) {
    try {
        $query = "SELECT * FROM roles WHERE id_rol = ?";
        $stmt = $connect->prepare($query);
        $stmt->execute([$idRol]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : false;
    } catch (PDOException $e) {
        // Handle database errors gracefully
        return false;
    }
}

