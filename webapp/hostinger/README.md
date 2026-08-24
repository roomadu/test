# Hostinger Deployment Guide

Upload everything inside **`public_html/`** to your Hostinger **`public_html`** folder.

## Step 1 — Upload files

```
your-hostinger-public_html/
├── index.php          ← Visitor form
├── admin.php          ← Admin dashboard
├── response.php       ← Employee approve/decline page
├── api/
│   ├── visit.php
│   ├── visits.php
│   ├── visit-get.php
│   ├── respond.php
│   └── export.php
├── includes/
│   ├── config.php     ← YOU MUST EDIT THIS
│   ├── config.example.php
│   ├── db.php
│   ├── mail.php
│   ├── helpers.php
│   ├── employees.php
│   └── .htaccess
└── .htaccess
```

## Step 2 — Create MySQL database

1. Hostinger hPanel → **Databases** → Create database + user
2. Note: database name, username, password, host (usually `localhost`)

## Step 3 — Configure

Copy `includes/config.example.php` to `includes/config.php` and edit:

```php
define('BASE_URL', 'https://yourdomain.com');  // Your live domain
define('DB_HOST', 'localhost');
define('DB_NAME', 'u123456789_visitors');
define('DB_USER', 'u123456789_admin');
define('DB_PASS', 'your_password');
define('EMAIL_USER', 'your@gmail.com');
define('EMAIL_PASS', 'gmail_app_password');
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'strong_password_here');
define('WHATSAPP_SETUP_URL', 'https://your-whatsapp-bridge.com/whatsapp-qr');
```

## Step 4 — Test

| Page | URL |
|------|-----|
| Visitor form | `https://yourdomain.com/` |
| Admin dashboard | `https://yourdomain.com/admin.php` |
| Employee response | `https://yourdomain.com/response.php?id=1` |

## Admin Dashboard Features

- **Visitor Records** — view all submissions, filter, search, download Excel
- **QR Generator** — generate QR for Visitor Form, WhatsApp Setup, or any custom URL
- **Quick Links** — copy links to share with employees

## WhatsApp on Hostinger

Shared hosting **cannot** run WhatsApp Web (needs Node.js + Chrome 24/7).

**Options:**
1. **Twilio WhatsApp** — set credentials in `config.php` (paid)
2. **WhatsApp Bridge** — keep the Node.js server running on a free VPS/Railway, set `WHATSAPP_BRIDGE_URL` in config
3. **Email only** — works without WhatsApp setup

Set `WHATSAPP_SETUP_URL` in config to the URL where employees scan to link WhatsApp.
Admin dashboard generates QR for that link automatically.

## How the system works

1. You create QR for visitor form link (from admin → QR Generator)
2. Print QR and place at office entrance
3. Visitor scans → fills form → submits → done (no response to visitor)
4. Employee gets **email + WhatsApp** with visitor details + Approve/Decline buttons
5. Employee clicks Approve/Decline → record updates in admin dashboard
6. Admin can download all records as Excel anytime

## Email on Hostinger

For Gmail: use an [App Password](https://myaccount.google.com/apppasswords).

For Hostinger email: use your Hostinger mailbox SMTP settings instead.

## No visitor limits

The database accepts unlimited visitor records. No caps are set.
