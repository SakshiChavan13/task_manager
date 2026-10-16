
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../helpers/response.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function authenticateRequest(): object
{
    $headers = function_exists('getallheaders')
        ? getallheaders()
        : [];

    $authorization = $headers['Authorization']
        ?? $headers['authorization']
        ?? $_SERVER['HTTP_AUTHORIZATION']
        ?? '';

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

        if (
            empty($decoded->sub) ||
            !isset($decoded->role) ||
            !in_array($decoded->role, ['admin', 'user'], true)
        ) {
            jsonResponse(401, [
                'success' => false,
                'message' => 'Invalid authentication token.'
            ]);
        }

        return $decoded;

    } catch (\Throwable $e) {
        jsonResponse(401, [
            'success' => false,
            'message' => 'Invalid or expired authentication token.'
        ]);
    }
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
