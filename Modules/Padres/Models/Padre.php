<?php

class Padre {
    private $id_pad;
    private $nombre;
    private $apellidos;
    private $correo;
    private $tel;
    private $estado_civil;
    private $dni;

    public function __construct(array $data) {
        $this->id_pad = $data['id_pad'];
        $this->nombre = $data['nombre_padre'];
        $this->apellidos = $data['apellidos_padre'];
        $this->correo = $data['correo_padre'];
        $this->tel = $data['telefono_padre'];
        $this->estado_civil = $data['estado_civil'];
        $this->dni = $data['dni_padre'];
    }

    // Getters
    public function getId() {
        return $this->id_pad;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getApellidos() {
        return $this->apellidos;
    }

    public function getCorreo() {
        return $this->correo;
    }

    public function getTel() {
        return $this->tel;
    }

    public function getEstadoCivil() {
        return $this->estado_civil;
    }

    public function getDni() {
        return $this->dni;
    }

    // Setters
    public function setId($id_pad) {
        $this->id_pad = $id_pad;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setApellidos($apellidos) {
        $this->apellidos = $apellidos;
    }

    public function setCorreo($correo) {
        $this->correo = $correo;
    }

    public function setTel($tel) {
        $this->tel = $tel;
    }

    public function setEstadoCivil($estado_civil) {
        $this->estado_civil = $estado_civil;
    }

    public function setDni($dni) {
        $this->dni = $dni;
    }
}