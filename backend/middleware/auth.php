
<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../config/database.php';

function authenticateRequest(): object
{
    global $pdo;

    $authorization = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? '';

    if (function_exists('getallheaders')) {
        foreach (getallheaders() as $name => $value) {
            if (strcasecmp($name, 'Authorization') === 0) {
                $authorization = $value;
                break;
            }
        }
    }

    if (
        !is_string($authorization) ||
        !preg_match('/^Bearer\s+(\S+)$/i', $authorization, $matches)
    ) {
        jsonResponse(401, [
            'success' => false,
            'message' => 'Authentication token is required.'
        ]);
    }

    $config = require __DIR__ . '/../config/jwt.php';

    try {
        $decoded = JWT::decode(
            $matches[1],
            new Key($config['secret'], 'HS256')
        );
    } catch (\Throwable $e) {
        jsonResponse(401, [
            'success' => false,
            'message' => 'Invalid or expired authentication token.'
        ]);
    }

    if (
        !isset($decoded->sub) ||
        !ctype_digit((string) $decoded->sub) ||
        (int) $decoded->sub < 1 ||
        !isset($decoded->jti) ||
        !is_string($decoded->jti) ||
        !preg_match('/^[a-f0-9]{32}$/', $decoded->jti)
    ) {
        jsonResponse(401, [
            'success' => false,
            'message' => 'Invalid authentication token.'
        ]);
    }

    $blacklistStmt = $pdo->prepare(
        'SELECT jti FROM token_blacklist WHERE jti = :jti LIMIT 1'
    );

    $blacklistStmt->execute(['jti' => $decoded->jti]);

    if ($blacklistStmt->fetch()) {
        jsonResponse(401, [
            'success' => false,
            'message' => 'This token has been logged out.'
        ]);
    }

    // Check the user against the database on every request.
    $stmt = $pdo->prepare(
        'SELECT id, name, email, role
         FROM users
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute(['id' => (int) $decoded->sub]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(401, [
            'success' => false,
            'message' => 'User account not found.'
        ]);
    }

    // Return the current database role, not the token's old role.
    return (object) [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'jti' => $decoded->jti,
        'exp' => $decoded->exp ?? null
    ];
}

function requireRole(object $user, string $role): void
{
    if (($user->role ?? null) !== $role) {
        jsonResponse(403, [
            'success' => false,
            'message' => 'You do not have permission to perform this action.'
        ]);
    }
}
