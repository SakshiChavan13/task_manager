
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;

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

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (
    !is_string($email) ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !is_string($password) ||
    $password === ''
) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Valid email and password are required.'
    ]);
}

try {
    $stmt = $pdo->prepare(
        'SELECT id, name, email, password, role
         FROM users
         WHERE email = :email'
    );

    $stmt->execute([
        'email' => strtolower(trim($email))
    ]);

    $user = $stmt->fetch();

    if (
        !$user ||
        !password_verify($password, $user['password'])
    ) {
        jsonResponse(401, [
            'success' => false,
            'message' => 'Invalid email or password.'
        ]);
    }

    $jwtConfig = require __DIR__ . '/../../config/jwt.php';

    $now = time();

    $payload = [
    'iss' => $jwtConfig['issuer'],
    'iat' => $now,
    'exp' => $now + $jwtConfig['expiry'],
    'sub' => (string) $user['id'],
    'role' => $user['role'],
    'jti' => bin2hex(random_bytes(16))
];

    $token = JWT::encode(
        $payload,
        $jwtConfig['secret'],
        'HS256'
    );

    jsonResponse(200, [
        'success' => true,
        'message' => 'Login successful.',
        'token' => $token,
        'expires_in' => $jwtConfig['expiry'],
        'user' => [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    jsonResponse(500, [
        'success' => false,
        'message' => 'Login failed.'
    ]);
}
