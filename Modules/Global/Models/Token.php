<?php

// Models/Token.php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Token {
    private static $secretKey;
    private static $staticToken;

    //Carga de la clave secreta y del token estatico
    public static function init() {
        if (!self::$secretKey) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../Config');
            $dotenv->load();
            self::$secretKey = $_ENV['ENCRYPTION_KEY'];
        }
    }

    //Comprobar que el token es valido al 100%
    public static function isValid($token, $type) {
        self::init(); // Asegura que esté todo cargado

        try {
            $decoded = JWT::decode($token, new Key(self::$secretKey, 'HS256'));
            return $decoded->type === $type && !self::isExpired($decoded->exp);
        } catch (\Exception $e) {
            return false;
        }
    }

    //Recoger los datos del token enviado
    public static function getUserByToken($token) {
        self::init();

        try {
            $decoded = JWT::decode($token, new Key(self::$secretKey, 'HS256'));
            return [
                'id' => $decoded->user_id,
                'email' => $decoded->email,
                'name' => $decoded->name
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    //Verificar tiempo del token
    public static function isExpired($exp) {
        return time() > $exp;
    }

    // Obtener el tiempo de expiracion
    public static function getExpiration($token) {
        self::init();
        try {
            $decoded = JWT::decode($token, new Key(self::$secretKey, 'HS256'));
            return $decoded->exp ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    //Usar metodo de arriba y restar el tiempo
    public static function timeLeft($token) {
        $exp = self::getExpiration($token);
        return $exp ? $exp - time() : 0;
    }    


    //Creacion del token
    public static function create($user) {
        self::init();

        $payload = [
            'user_id' => $user['user_id'],
            'email' => $user['correo'],
            'name' => $user['nombre'],
            'type' => $user['type'],
            'exp' => time() + 3600 // 1 hora
        ];

        return JWT::encode($payload, self::$secretKey, 'HS256');
    }

    public static function createToken($user) {
        self::init();

        $payload = [
            'user_id' => $user['user_id'],
            'email' => $user['correo'],
            'name' => $user['nombre'],
            'type' => $user['type'],
            'exp' => time() + 60 * 60 * 24 * 30 // 30 dias
        ];

        return JWT::encode($payload, self::$secretKey, 'HS256');
    }
}
