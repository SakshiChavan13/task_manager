
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

// This endpoint currently supports GET only.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');

    jsonResponse(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

// Validate optional status filter.
$status = $_GET['status'] ?? null;

$allowedStatuses = ['todo', 'in-progress', 'done'];

if ($status !== null && !in_array($status, $allowedStatuses, true)) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Invalid status.',
        'allowed_statuses' => $allowedStatuses
    ]);
}

// Validate pagination parameters.
$pageInput = $_GET['page'] ?? '1';
$limitInput = $_GET['limit'] ?? '10';

if (
    !is_string($pageInput) ||
    !ctype_digit($pageInput) ||
    (int) $pageInput < 1 ||
    !is_string($limitInput) ||
    !ctype_digit($limitInput) ||
    (int) $limitInput < 1
) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Page and limit must be positive integers.'
    ]);
}

$page = (int) $pageInput;
$limit = min((int) $limitInput, 100);
$offset = ($page - 1) * $limit;

try {
    // Build the optional filter safely.
    $where = '';
    $params = [];

    if ($status !== null) {
        $where = ' WHERE status = :status';
        $params['status'] = $status;
    }

    // Count matching tasks for pagination metadata.
    $countSql = "SELECT COUNT(*) FROM tasks" . $where;
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);

    $total = (int) $countStmt->fetchColumn();

    // Fetch the current page.
    $sql = "SELECT
                id,
                user_id,
                title,
                description,
                status,
                priority,
                due_date,
                created_at,
                updated_at
            FROM tasks"
            . $where .
            " ORDER BY id DESC
              LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();

    $tasks = $stmt->fetchAll();

    jsonResponse(200, [
        'success' => true,
        'data' => $tasks,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $total,
            'total_pages' => (int) ceil($total / $limit)
        ]
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    jsonResponse(500, [
        'success' => false,
        'message' => 'Failed to retrieve tasks.'
    ]);
}
