<?php

class Database
{
    public static function conectar(string $driver): PDO
    {
        // 1. Alterado 'host=db' para 'host=127.0.0.1' (ou 'localhost')
        $dsn = ['mysql' => 'mysql:host=127.0.0.1;port=3306;dbname=biblioteca;charset=utf8mb4'];
        
        if (!isset($dsn[$driver])) throw new InvalidArgumentException('Driver inválido');
        
        // 2. Substitua 'root' e '' pelas suas credenciais locais se não usar getenv()
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '12345'; // Coloque sua senha aqui se houver
        
        return new PDO($dsn[$driver], $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}