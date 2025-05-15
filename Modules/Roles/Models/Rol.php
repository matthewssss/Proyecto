<?php

class Rol {
    private $id;
    private $nombre;
    private $permisos;

    // Constructor
    public function __construct($id, $nombre, $permisos) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->permisos = json_decode($permisos, true); // Assuming permisos is stored as a JSON string
    }

    // Getter for id
    public function getId() {
        return $this->id;
    }

    // Getter for nombre
    public function getNombre() {
        return $this->nombre;
    }

    // Function to check if a role has a specific permission
    public function tienePermiso($permiso) {
        return in_array($permiso, $this->permisos);
    }

    public function getPermisos () {
        return $this -> permisos;
    }
}