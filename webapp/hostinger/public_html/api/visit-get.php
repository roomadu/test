<?php
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    jsonResponse(['error' => 'Missing id'], 400);
}

try {
    $stmt = db()->prepare('SELECT * FROM visits WHERE id = ?');
    $stmt->execute([$id]);
    $visit = $stmt->fetch();
    if (!$visit) {
        jsonResponse(['error' => 'Not found'], 404);
    }
    jsonResponse(['visit' => $visit]);
} catch (Throwable $e) {
    error_log('Visit-get error: ' . $e->getMessage());
    jsonResponse(['error' => dbErrorMessage($e)], 500);
}
