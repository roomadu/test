<?php

function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function baseUrl(): string
{
    return rtrim(BASE_URL, '/');
}

function employeeDirectory(): array
{
    return require __DIR__ . '/employees.php';
}

function buildEmployeeEmailHtml(array $visit, string $approveLink, string $rejectLink): string
{
    $rows = [
        ['Visitor Name', htmlspecialchars($visit['visitorName'])],
        ['Organization', htmlspecialchars($visit['organization'] ?: '—')],
        ['Whom to Meet', htmlspecialchars($visit['whomToMeet'])],
        ['Date', htmlspecialchars($visit['date'])],
        ['Purpose', htmlspecialchars($visit['purpose'])],
        ['Number of People', (int)($visit['numPeople'] ?? 1)],
    ];

    $details = '';
    foreach ($rows as [$label, $value]) {
        $details .= "<tr>
            <td style=\"padding:10px 14px;border-bottom:1px solid #eef2f7;color:#64748b;font-size:13px;width:38%\">{$label}</td>
            <td style=\"padding:10px 14px;border-bottom:1px solid #eef2f7;color:#0f172a;font-size:14px;font-weight:600\">{$value}</td>
        </tr>";
    }

    return "
    <div style=\"font-family:Segoe UI,Arial,sans-serif;background:#f8fafc;padding:24px\">
        <div style=\"max-width:620px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden\">
            <div style=\"background:#4f46e5;color:#fff;padding:20px 24px\">
                <div style=\"font-size:12px;letter-spacing:1px;text-transform:uppercase;opacity:.85\">New Visitor Request</div>
                <div style=\"font-size:22px;font-weight:700;margin-top:6px\">" . htmlspecialchars($visit['visitorName']) . "</div>
            </div>
            <table style=\"width:100%;border-collapse:collapse\">{$details}</table>
            <div style=\"padding:20px 24px 28px;text-align:center\">
                <a href=\"{$approveLink}\" style=\"display:inline-block;padding:12px 28px;background:#059669;color:#fff;text-decoration:none;border-radius:8px;font-weight:700;margin-right:10px\">Approve</a>
                <a href=\"{$rejectLink}\" style=\"display:inline-block;padding:12px 28px;background:#dc2626;color:#fff;text-decoration:none;border-radius:8px;font-weight:700\">Decline</a>
            </div>
        </div>
    </div>";
}

function buildEmployeeWhatsAppText(array $visit, string $approveLink, string $rejectLink): string
{
    return "New Visitor Request\n\n"
        . "Name: {$visit['visitorName']}\n"
        . "Organization: " . ($visit['organization'] ?: '—') . "\n"
        . "Whom to Meet: {$visit['whomToMeet']}\n"
        . "Date: {$visit['date']}\n"
        . "Purpose: {$visit['purpose']}\n"
        . "People: " . ($visit['numPeople'] ?? 1) . "\n\n"
        . "Approve: {$approveLink}\n"
        . "Decline: {$rejectLink}";
}

function formatPhoneForWhatsApp(string $phone): string
{
    $phone = preg_replace('/\D+/', '', $phone);
    if (substr($phone, 0, 1) === '0') {
        $phone = '94' . substr($phone, 1);
    }
    return $phone;
}
