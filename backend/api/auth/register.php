
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    jsonResponse(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    jsonResponse(400, [
        'success' => false,
        'message' => 'Invalid JSON request body.'
    ]);
}

$name = $input['name'] ?? '';
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (
    !is_string($name) ||
    trim($name) === '' ||
    mb_strlen(trim($name)) > 100
) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Name is required and must be at most 100 characters.'
    ]);
}

if (
    !is_string($email) ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 255
) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'A valid email address is required.'
    ]);
}

if (
    !is_string($password) ||
    strlen($password) < 8 ||
    strlen($password) > 72
) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Password must contain 8 to 72 bytes.'
    ]);
}

try {
    $stmt = $pdo->prepare(
        'SELECT id FROM users WHERE email = :email'
    );
    $stmt->execute(['email' => strtolower(trim($email))]);

    if ($stmt->fetch()) {
        jsonResponse(409, [
            'success' => false,
            'message' => 'An account with this email already exists.'
        ]);
    }

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $stmt = $pdo->prepare(
        'INSERT INTO users (name, email, password, role)
         VALUES (:name, :email, :password, :role)'
    );

    $stmt->execute([
        'name' => trim($name),
        'email' => strtolower(trim($email)),
        'password' => $passwordHash,
        'role' => 'user'
    ]);

    jsonResponse(201, [
        'success' => true,
        'message' => 'Registration successful.',
        'data' => [
            'id' => (int) $pdo->lastInsertId(),
            'name' => trim($name),
            'email' => strtolower(trim($email)),
            'role' => 'user'
        ]
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    jsonResponse(500, [
        'success' => false,
        'message' => 'Registration failed.'
    ]);
}
