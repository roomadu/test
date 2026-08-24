<?php
/**
 * Employees open this page (or scan its QR) to connect WhatsApp to the system.
 * Redirects to WHATSAPP_SETUP_URL where they scan with WhatsApp Linked Devices.
 */
require_once __DIR__ . '/includes/config.php';

$whatsappConfigured = defined('WHATSAPP_SETUP_URL') && WHATSAPP_SETUP_URL
    && strpos(WHATSAPP_SETUP_URL, 'your-node-server') === false;

if ($whatsappConfigured) {
    header('Location: ' . WHATSAPP_SETUP_URL);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Setup</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; color: #111827; }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 2rem; max-width: 480px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        h1 { font-size: 1.2rem; margin-bottom: 0.5rem; }
        p { color: #6b7280; font-size: 0.9rem; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="card">
        <h1>WhatsApp Setup</h1>
        <p>WhatsApp is not configured yet. Admin must set <strong>WHATSAPP_SETUP_URL</strong> in includes/config.php.</p>
    </div>
</body>
</html>
