<?php

$host = 'db';
$db   = 'ace';
$user = 'ace';
$pass = 'ace';
$port = '5432';

$dsn = "pgsql:host=$host;port=$port;dbname=$db;";

try {
     $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
     if ($pdo) {
         echo "Successfully connected to the database: $db";
     }
} catch (PDOException $e) {
     echo "Connection failed: " . $e->getMessage();
}

//<?php
//
//return [
//    'host' => $_ENV['DB_HOST'],
//    'port' => $_ENV['DB_PORT'],
//    'database' => $_ENV['DB_DATABASE'],
//    'username' => $_ENV['DB_USERNAME'],
//    'password' => $_ENV['DB_PASSWORD']
//];