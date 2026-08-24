<?php
require_once __DIR__ . '/../includes/db.php';

session_start();
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    exit('Unauthorized');
}

try {
    $stmt = db()->query('SELECT * FROM visits ORDER BY createdAt DESC');
    $rows = $stmt->fetchAll();

    $headers = ['ID', 'Visitor Name', 'Organization', 'Whom to Meet', 'Date', 'Purpose', 'People', 'Status', 'Meeting Time', 'Note', 'Submitted At'];

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="visitor-records-' . date('Y-m-d') . '.csv"');

    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, $headers);

    foreach ($rows as $v) {
        fputcsv($out, [
            $v['id'],
            $v['visitorName'],
            $v['organization'],
            $v['whomToMeet'],
            $v['date'],
            $v['purpose'],
            $v['numPeople'],
            $v['status'],
            $v['meetingTime'],
            $v['note'],
            $v['createdAt'],
        ]);
    }
    fclose($out);
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Export failed';
}
