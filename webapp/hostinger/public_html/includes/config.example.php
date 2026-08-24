<?php
/**
 * Copy this file to config.php and fill in your Hostinger details.
 */

// Your live website URL (no trailing slash)
define('BASE_URL', 'https://yourdomain.com');

// MySQL — use Hostinger hPanel → Databases
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');

// Gmail SMTP (use App Password)
define('EMAIL_USER', 'your_email@gmail.com');
define('EMAIL_PASS', 'your_app_password');
define('EMAIL_FROM_NAME', 'Visitor System');

// Admin login (change this password!)
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'change_this_password');

// ── WhatsApp (employees scan QR for this link to connect WhatsApp) ──
// Leave BOTH blank until you deploy the Node.js WhatsApp bridge somewhere
// public and always-on (a VPS, Railway, Render, etc). Shared hosting cannot
// run it. Until then, visitor alerts are still sent by email.
// Point to your Node.js WhatsApp server, e.g. https://your-vps.com:3000/whatsapp-qr
// Admin dashboard → WhatsApp QR tab shows a QR for whichever link you set here.
define('WHATSAPP_SETUP_URL', '');

// PHP on Hostinger sends WhatsApp messages through this Node server API
define('WHATSAPP_BRIDGE_URL', '');

// Optional: Twilio WhatsApp instead of Node bridge
define('TWILIO_ACCOUNT_SID', '');
define('TWILIO_AUTH_TOKEN', '');
define('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886');

define('TIMEZONE', 'Asia/Colombo');
