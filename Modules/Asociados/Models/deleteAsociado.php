<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function deleteAsociado($connect) {
    $idAsociado = $_POST['idAsociado'];
    $idPadre = $_POST['idPadre'];

    try {
        // 1. Actualizar el asociado a eliminado
        $stmt = $connect->prepare("
            UPDATE inscripcion_asociados 
            SET eliminado = 1, fecha_eliminacion = :fecha 
            WHERE id_ins = :id
        ");
        $stmt->execute([
            ':fecha' => date('Y-m-d H:i:s'),
            ':id' => $idAsociado
        ]);

        // 2. Comprobar si el padre tiene más asociados activos
        $stmt = $connect->prepare("
            SELECT COUNT(*) as total 
            FROM padre_inscrito pi 
            JOIN inscripcion_asociados ia ON ia.id_ins = pi.id_ins 
            WHERE pi.id_pad = :idPadre AND ia.eliminado = 0
        ");
        $stmt->execute([':idPadre' => $idPadre]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($res && intval($res['total']) === 0) {
            // 3. Si no tiene más asociados activos, marcar también al padre como eliminado
            $stmt = $connect->prepare("
                UPDATE padres 
                SET eliminado = 1, fecha_eliminacion = :fecha 
                WHERE id_pad = :id
            ");
            $stmt->execute([
                ':fecha' => date('Y-m-d H:i:s'),
                ':id' => $idPadre
            ]);
        }

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['error' => true, 'msg' => $e->getMessage()]);
    }
}