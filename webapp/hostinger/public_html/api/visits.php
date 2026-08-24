<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_logged_in'])) {
    jsonResponse(['error' => 'Unauthorized'], 403);
}

try {
    $stmt = db()->query('SELECT * FROM visits ORDER BY createdAt DESC');
    jsonResponse(['visits' => $stmt->fetchAll()]);
} catch (Throwable $e) {
    error_log('Visits load error: ' . $e->getMessage());
    jsonResponse(['error' => dbErrorMessage($e)], 500);
}
