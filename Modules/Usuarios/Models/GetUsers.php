<?php
    function getUsers($connect) {
        $query = "SELECT id_usr, nombre, apellidos, correo, tel, id_rol FROM usuarios";
        $stmt = $connect->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($data) {
            return $data;
        } else {
            return json_encode(['error' => true, 'message' => 'Error fetching users']);
        }
    }

    function getInfoMonitores($connect) {
        $query = "SELECT id_usr, rama FROM monitores";
        $stmt = $connect->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }