
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');

    jsonResponse(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

$user = authenticateRequest();

$stmt = $pdo->prepare(
    'INSERT IGNORE INTO token_blacklist (jti, expires_at)
     VALUES (:jti, :expires_at)'
);

$stmt->execute([
    'jti' => $user->jti,
    'expires_at' => $user->exp
]);

jsonResponse(200, [
    'success' => true,
    'message' => 'Logged out successfully.'
]);
