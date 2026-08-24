<?php
require_once __DIR__ . '/../includes/db.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    jsonResponse(['error' => 'Missing id'], 400);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$status = trim($input['status'] ?? '');
$meetingTime = trim($input['meetingTime'] ?? '') ?: null;
$note = trim($input['note'] ?? '') ?: null;

if (!in_array($status, ['Approved', 'Rejected'], true)) {
    jsonResponse(['error' => 'Invalid status'], 400);
}

try {
    $stmt = db()->prepare('UPDATE visits SET status = ?, meetingTime = ?, note = ? WHERE id = ?');
    $stmt->execute([$status, $meetingTime, $note, $id]);
    jsonResponse(['success' => true]);
} catch (Throwable $e) {
    error_log('Respond error: ' . $e->getMessage());
    jsonResponse(['error' => dbErrorMessage($e)], 500);
}
