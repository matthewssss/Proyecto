<?php

function updateData ($connect) {
    // Obtener los datos del asociado
    $asociadoData = $_POST['asociado'];
    // Obtener los datos del padre
    $padreData = $_POST['padre'];

    $asociadoData = [
        'nombre' => encryptField($asociadoData['nombre']),
        'apellidos' => encryptField($asociadoData['apellidos']),
        'fecha_nacimiento' => encryptField($asociadoData['fecha_nacimiento']),
        'edad' => encryptField($asociadoData['edad']),
        'unidad' => $asociadoData['unidad'],
        'comunidadId' => encryptField($asociadoData['comunidadId']),
        'provinciaId' => encryptField($asociadoData['provinciaId']),
        'municipioId' => encryptField($asociadoData['municipioId']),
        'idAsociado' => $asociadoData['idAsociado']
    ];

    // Actualizar datos del asociado
    $sqlAsociado = "UPDATE inscripcion_asociados SET 
                        nombre = :nombre, 
                        apellidos = :apellidos, 
                        fecha_nacimiento = :fecha_nacimiento,
                        edad = :edad,
                        unidad = :unidad, 
                        comunidad_autonoma = :comunidadId, 
                        provincia = :provinciaId, 
                        municipio = :municipioId
                    WHERE id_ins = :asociadoId";

    $stmt = $connect->prepare($sqlAsociado);
    $stmt->execute([
        ':nombre' => $asociadoData['nombre'],
        ':apellidos' => $asociadoData['apellidos'],
        ':fecha_nacimiento' => $asociadoData['fecha_nacimiento'],
        ':edad' => $asociadoData['edad'],
        ':unidad' => $asociadoData['unidad'],
        ':comunidadId' => $asociadoData['comunidadId'],
        ':provinciaId' => $asociadoData['provinciaId'],
        ':municipioId' => $asociadoData['municipioId'],
        ':asociadoId' => $asociadoData['idAsociado'] // Asegúrate de pasar el ID correcto
    ]);

    $padreData = [
        'nombre' => encryptField($padreData['nombre']),
        'apellidos' => encryptField($padreData['apellidos']),
        'correo' => encryptField($padreData['correo']),
        'telefono' => encryptField($padreData['telefono']),
        'estadoCivil' => encryptField($padreData['estadoCivil']),
        'idPadre' => $padreData['idPadre']
    ];

    // Actualizar datos del padre
    $sqlPadre = "UPDATE padres SET 
                        nombre = :padreNombre, 
                        apellidos = :padreApellidos, 
                        correo = :padreCorreo, 
                        tel = :padreTelefono, 
                        estado_civil = :padreEstadoCivil
                    WHERE id_pad = :padreId"; // Suponiendo que identificas al padre por su DNI

    $stmtPadre = $connect->prepare($sqlPadre);
    $stmtPadre->execute([
        ':padreNombre' => $padreData['nombre'],
        ':padreApellidos' => $padreData['apellidos'],
        ':padreCorreo' => $padreData['correo'],
        ':padreTelefono' => $padreData['telefono'],
        ':padreEstadoCivil' => $padreData['estadoCivil'],
        ':padreId' => $padreData['idPadre'] // Asegúrate de pasar el DNI correcto
    ]);

    echo json_encode(['success' => true]);    
}