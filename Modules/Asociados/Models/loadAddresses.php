<?php

function getComunidades ($connect) {
    $query = "SELECT idCCAA, Nombre FROM CCAA";
    $results = $connect->prepare($query);
    $results->execute();;
    $comunidades = [];

    if ($results->rowCount() > 0) {
        foreach ($results as $row) {
            $comunidades[] = [
                'id' => $row['idCCAA'],
                'nombre' => $row['Nombre']
            ];
        }
        
        return json_encode([
            'error' => false,
            'msg' => $comunidades
        ]);
    } else {
        return [
            'error' => true,
            'msg' => 'Error al obtener las comunidades'
        ];
    }
}

function getProvincias ($connect, $comunidadId) {
    $query = "SELECT idProvincia, Provincia FROM PROVINCIAS WHERE idCCAA = :comunidadId";
    $results = $connect->prepare($query);
    $results->bindParam(':comunidadId', $comunidadId);
    $results->execute();
    $provincias = [];
    if ($results->rowCount() > 0) {
        foreach ($results as $row) {
            $provincias[] = [
                'id' => $row['idProvincia'],
                'nombre' => $row['Provincia']
            ];
        }
        return json_encode([
            'error' => false,
            'msg' => $provincias
        ]);
    } else {
        return [
            'error' => true,
            'msg' => 'Error al obtener las provincias'
        ];
    }
}

function getMunicipios ($connect, $provinciaId) {
    $query = "SELECT idMunicipio, Municipio FROM MUNICIPIOS WHERE idProvincia = :provinciaId";
    $results = $connect->prepare($query);
    $results->bindParam(':provinciaId', $provinciaId);
    $results->execute();
    $municipios = [];

    if ($results->rowCount() > 0) {
        foreach ($results as $row) {
            $municipios[] = [
                'id' => $row['idMunicipio'],
                'nombre' => $row['Municipio']
            ];
        }
        
        return json_encode([
            'error' => false,
            'msg' => $municipios
        ]);
    } else {
        return [
            'error' => true,
            'msg' => 'Error al obtener los municipios'
        ];
    }
}