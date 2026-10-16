
<?php

require_once __DIR__ . '/../helpers/response.php';

$host = '127.0.0.1';
$dbname = 'task_manager';
$username = 'root';
$password = ''; // Default Laragon setup; verify your MySQL credentials.

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());

    jsonResponse(500, [
        'success' => false,
        'message' => 'Database connection failed.'
    ]);
}
