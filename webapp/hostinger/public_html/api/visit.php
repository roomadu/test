<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mail.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$visitorName = trim($input['visitorName'] ?? '');
$organization = trim($input['organization'] ?? '');
$whomToMeet = trim($input['whomToMeet'] ?? '');
$date = trim($input['date'] ?? '');
$purpose = trim($input['purpose'] ?? '');
$numPeople = (int)($input['numPeople'] ?? 1);

if (!$visitorName || !$whomToMeet || !$date || !$purpose) {
    jsonResponse(['error' => 'Missing required fields'], 400);
}

try {
    $stmt = db()->prepare(
        'INSERT INTO visits (visitorName, visitorEmail, visitorPhone, organization, whomToMeet, date, purpose, numPeople)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$visitorName, '', '', $organization, $whomToMeet, $date, $purpose, max(1, $numPeople)]);
    $visitId = (int)db()->lastInsertId();

    $visit = [
        'visitorName' => $visitorName,
        'organization' => $organization,
        'whomToMeet' => $whomToMeet,
        'date' => $date,
        'purpose' => $purpose,
        'numPeople' => max(1, $numPeople),
    ];

    $base = baseUrl();
    $approveLink = "{$base}/response.php?id={$visitId}&action=approve";
    $rejectLink = "{$base}/response.php?id={$visitId}&action=reject";

    $directory = employeeDirectory();
    $host = $directory[$whomToMeet] ?? $directory['Other'];

    $messageHtml = buildEmployeeEmailHtml($visit, $approveLink, $rejectLink);
    $messageText = buildEmployeeWhatsAppText($visit, $approveLink, $rejectLink);

    sendEmail($host['email'], "New Visitor: {$visitorName}", $messageText, $messageHtml);
    sendWhatsAppMessage($host['phone'], $messageText);

    jsonResponse(['success' => true, 'id' => $visitId]);
} catch (Throwable $e) {
    error_log('Visit submit error: ' . $e->getMessage());
    jsonResponse(['error' => dbErrorMessage($e)], 500);
}
