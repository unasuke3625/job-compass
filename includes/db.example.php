<?php

$host = 'localhost';
$dbname = 'job_compass';
$username = 'your_username';
$password = 'your_password';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    exit('Database connection failed.');
}