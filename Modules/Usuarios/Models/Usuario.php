<?php

class Usuario {
    private $id_usr;
    private $nombre;
    private $apellidos;
    private $correo;
    private $tel;
    private $contraseña;
    private $rol;
    private $token;
    private $token_expiration;

    // Constructor accepting an associative array
    public function __construct(array $data) {
        $this->id_usr = $data['id_usr'];
        $this->nombre = $data['nombre'];
        $this->apellidos = $data['apellidos'];
        $this->correo = $data['correo'];
        $this->tel = $data['tel'] ?? null;
        $this->contraseña = $data['password'];
        $this->token = $data['token'];
        $this->token_expiration = $data['token_expiration'];

        $this->rol = null;
    }

    // Getters and Setters
    public function getIdUsr() {
        return $this->id_usr;
    }

    public function setIdUsr($id_usr) {
        $this->id_usr = $id_usr;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function getApellidos() {
        return $this->apellidos;
    }

    public function setApellidos($apellidos) {
        $this->apellidos = $apellidos;
    }

    public function getCorreo() {
        return $this->correo;
    }

    public function setCorreo($correo) {
        $this->correo = $correo;
    }

    public function getTel() {
        return $this->tel;
    }

    public function setTel($tel) {
        $this->tel = $tel;
    }

    public function getContraseña() {
        return $this->contraseña;
    }

    public function setContraseña($contraseña) {
        $this->contraseña = $contraseña;
    }

    public function getToken() {
        return $this->token;
    }

    public function setToken($token) {
        $this->token = $token;
    }

    public function getTokenExpiration() {
        return $this->token_expiration;
    }

    public function setTokenExpiration($token_expiration) {
        $this->token_expiration = $token_expiration;
    }

    /* 
    * Agregar el rol al usuario
    * @param array $data 
    */

    public function setRol ($data) {
        $this->rol = new Rol($data['id_rol'], $data['nombre'], $data['permisos']);;
    }

    public function getIdRol() {
        return $this->rol->getId();
    }

    public function tienePermiso ($permiso) {
        return $this->rol->tienePermiso($permiso);
    }

    public function getPermisos () {
        return $this->rol->getPermisos();
    }

    public function getEditData () {
        return [
            'id_usr' => $this->id_usr,
            'nombre' => $this->nombre,
            'apellidos' => $this->apellidos,
            'correo' => $this->correo,
            'tel' => $this->tel,
            'contraseña' => $this->contraseña
        ];
    }
}