<?php
    // Cargar el autoload de Composer
    require_once __DIR__ . '/../Vendor/Backend/autoload.php';

    // Usar Dotenv para cargar las variables de entorno
    use Dotenv\Dotenv;

    // Cargar .env desde el directorio Config
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../Config'); 
    $dotenv->load();


    try {
        $server = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];


        $dsn = "mysql:host=$server;dbname=$dbname;charset=utf8mb4";
        $conexion = new PDO($dsn, $user, $pass);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }