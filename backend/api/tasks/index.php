
<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../helpers/cors.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');

    jsonResponse(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

$currentUser = authenticateRequest();

$status = $_GET['status'] ?? null;
$page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
$limit = filter_var($_GET['limit'] ?? 10, FILTER_VALIDATE_INT);

if ($page === false || $page < 1) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Page must be a positive integer.'
    ]);
}

if ($limit === false || $limit < 1) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Limit must be a positive integer.'
    ]);
}

$limit = min($limit, 100);
$offset = ($page - 1) * $limit;

$allowedStatuses = ['todo', 'in-progress', 'done'];

if ($status !== null && !in_array($status, $allowedStatuses, true)) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Invalid task status.'
    ]);
}

$conditions = [];
$params = [];

// Regular users can only access their own tasks.
if ($currentUser->role !== 'admin') {
    $conditions[] = 'user_id = :user_id';
    $params['user_id'] = $currentUser->id;
}

if ($status !== null) {
    $conditions[] = 'status = :status';
    $params['status'] = $status;
}

$whereSql = $conditions
    ? ' WHERE ' . implode(' AND ', $conditions)
    : '';

// Count matching tasks for pagination.
$countStmt = $pdo->prepare(
    'SELECT COUNT(*) FROM tasks' . $whereSql
);
$countStmt->execute($params);

$total = (int) $countStmt->fetchColumn();

// Fetch the requested page.
$sql = 'SELECT id, user_id, title, description, status,
               priority, due_date, created_at, updated_at
        FROM tasks'
        . $whereSql .
        ' ORDER BY id DESC
          LIMIT :limit OFFSET :offset';

$stmt = $pdo->prepare($sql);

foreach ($params as $key => $value) {
    $stmt->bindValue(
        ':' . $key,
        $value,
        $key === 'user_id' ? PDO::PARAM_INT : PDO::PARAM_STR
    );
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

jsonResponse(200, [
    'success' => true,
    'data' => $stmt->fetchAll(),
    'pagination' => [
        'page' => $page,
        'limit' => $limit,
        'total' => $total,
        'total_pages' => (int) ceil($total / $limit)
    ]
]);
