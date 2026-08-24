require('dotenv').config();
require('dns').setDefaultResultOrder('ipv4first');
const express = require('express');
const cors = require('cors');
const path = require('path');
const os = require('os');
const fs = require('fs');
const nodemailer = require('nodemailer');
const { Client, LocalAuth } = require('whatsapp-web.js');
const QRCode = require('qrcode');

const promisePool = require('./database');

const app = express();
const PORT = process.env.PORT || 3000;

// Get local network IP for links that need to work from other devices
const getServerIP = () => {
    const interfaces = os.networkInterfaces();
    for (const name of Object.keys(interfaces)) {
        for (const iface of interfaces[name]) {
            if (iface.family === 'IPv4' && !iface.internal) {
                return iface.address;
            }
        }
    }
    return 'localhost';
};

// Public base URL for links in emails (set BASE_URL in .env for production)
const getBaseUrl = () => {
    if (process.env.BASE_URL) return process.env.BASE_URL.replace(/\/$/, '');
    return `http://${getServerIP()}:${PORT}`;
};

const buildEmployeeEmailHtml = (visit, approveLink, rejectLink) => {
    const rows = [
        ['Visitor Name', visit.visitorName],
        ['Organization', visit.organization || '—'],
        ['Whom to Meet', visit.whomToMeet],
        ['Date', visit.date],
        ['Purpose', visit.purpose],
        ['Number of People', visit.numPeople || 1],
    ];
    const details = rows.map(([label, value]) => `
        <tr>
            <td style="padding:10px 14px;border-bottom:1px solid #eef2f7;color:#64748b;font-size:13px;width:38%;">${label}</td>
            <td style="padding:10px 14px;border-bottom:1px solid #eef2f7;color:#0f172a;font-size:14px;font-weight:600;">${value}</td>
        </tr>
    `).join('');

    return `
    <div style="font-family:Segoe UI,Arial,sans-serif;background:#f8fafc;padding:24px;">
        <div style="max-width:620px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
            <div style="background:#4f46e5;color:#fff;padding:20px 24px;">
                <div style="font-size:12px;letter-spacing:1px;text-transform:uppercase;opacity:0.85;">New Visitor Request</div>
                <div style="font-size:22px;font-weight:700;margin-top:6px;">${visit.visitorName}</div>
            </div>
            <div style="padding:8px 0 4px;">
                <table style="width:100%;border-collapse:collapse;">${details}</table>
            </div>
            <div style="padding:20px 24px 28px;text-align:center;">
                <a href="${approveLink}" style="display:inline-block;padding:12px 28px;background:#059669;color:#fff;text-decoration:none;border-radius:8px;font-weight:700;margin-right:10px;">Approve</a>
                <a href="${rejectLink}" style="display:inline-block;padding:12px 28px;background:#dc2626;color:#fff;text-decoration:none;border-radius:8px;font-weight:700;">Decline</a>
            </div>
        </div>
    </div>`;
};

const buildEmployeeWhatsAppText = (visit, approveLink, rejectLink) =>
    `NEW VISITOR REQUEST\n\n` +
    `*Visitor Name:* ${visit.visitorName}\n` +
    `*Organization:* ${visit.organization || '—'}\n` +
    `*Whom to Meet:* ${visit.whomToMeet}\n` +
    `*Date:* ${visit.date}\n` +
    `*Purpose:* ${visit.purpose}\n` +
    `*No. of People:* ${visit.numPeople || 1}\n\n` +
    `*Approve:* ${approveLink}\n` +
    `*Decline:* ${rejectLink}`;

let waClient = null;
let waReady = false;
let latestQR = null;
let waInitializing = false;

// Find a real Chrome/Edge install so we don't need to download Chromium.
// Falls back to Puppeteer's own bundled browser if none of these exist.
const findChromePath = () => {
    const candidates = [
        process.env.CHROME_PATH,
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        '/usr/bin/google-chrome',
        '/usr/bin/google-chrome-stable',
        '/usr/bin/chromium-browser',
        '/usr/bin/chromium',
    ].filter(Boolean);
    return candidates.find((p) => {
        try { return fs.existsSync(p); } catch (_) { return false; }
    });
};

const initWhatsApp = () => {
    if (waInitializing) return;
    waInitializing = true;
    waReady = false;
    latestQR = null;

    const chromePath = findChromePath();
    if (chromePath) {
        console.log('Using browser at:', chromePath);
    } else {
        console.log('No system Chrome/Edge found, using Puppeteer\'s bundled Chromium.');
    }

    waClient = new Client({
        authStrategy: new LocalAuth(),
        puppeteer: {
            headless: true,
            ...(chromePath ? { executablePath: chromePath } : {}),
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        }
    });

    waClient.on('qr', (qr) => {
        latestQR = qr;
        console.log(' QR code ready! Open http://localhost:3000/whatsapp-qr to scan it.');
    });

    waClient.on('ready', () => {
        waReady = true;
        waInitializing = false;
        latestQR = null;
        console.log(' WhatsApp is connected and ready to send messages!');
    });

    waClient.on('disconnected', () => {
        waReady = false;
        waInitializing = false;
        console.log('WhatsApp disconnected.');
    });

    // Avoid crashing the whole server if WhatsApp auth hangs/timeouts.
    waClient.initialize().catch((err) => {
        console.error('WhatsApp init error:', err?.message || err);
        waReady = false;
        waInitializing = false;
        latestQR = null;
        // Retry after a short delay
        setTimeout(() => initWhatsApp(), 5000);
    });
};

// Set DISABLE_WHATSAPP=true (e.g. on managed hosts without a real Chrome
// browser, like Hostinger's Node.js Web App containers) to skip WhatsApp
// entirely instead of retrying forever. Email notifications still work.
if (process.env.DISABLE_WHATSAPP === 'true') {
    console.log('WhatsApp disabled via DISABLE_WHATSAPP env var. Email notifications will still be sent.');
} else {
    initWhatsApp();
}

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use((req, res, next) => {
    if (req.path.endsWith('.html') || req.path === '/') {
        res.set('Cache-Control', 'no-store, no-cache, must-revalidate');
        res.set('Pragma', 'no-cache');
    }
    next();
});
app.use(express.static(path.join(__dirname, 'public')));

const transporter = nodemailer.createTransport({
    host: 'smtp.gmail.com',
    port: 465,
    secure: true,
    auth: {
        user: process.env.EMAIL_USER,
        pass: process.env.EMAIL_PASS
    },
    tls: {
        rejectUnauthorized: false
    }
});

const sendWhatsAppMessage = async (to, body) => {
    if (waReady && waClient) {
        try {
            // Format the number: remove leading 0, add Sri Lanka country code +94
            const formattedTo = to.startsWith('+')
                ? to.replace('+', '') + '@c.us'
                : `94${to.replace(/^0/, '')}@c.us`;

            await waClient.sendMessage(formattedTo, body);
            console.log(` WhatsApp sent to ${formattedTo}`);
        } catch (err) {
            console.error('WhatsApp send error:', err.message);
        }
    } else {
        console.log(`[WhatsApp NOT READY] Message to ${to}:\n${body}`);
        console.log('Tip: Scan the QR code in the terminal when you start the server.');
    }
};

// Employee Directory Mapping
const employeeDirectory = {
    "Marc Perera (Chief Digital Officer)": { email: "binadilaknara2004@gmail.com", phone: "0776165487" },
    "Hashantha Hemachandra (General Manager (Designate) - Group Digital)": { email: "hashantha@example.com", phone: "0000000000" },
    "Ishara Nanayakkarawasam (Director - Digital Content)": { email: "ishara@example.com", phone: "0000000000" },
    "Chamara Silva (Performance Marketing Manager)": { email: "chamara@example.com", phone: "0000000000" },
    "Other": { email: "admin@example.com", phone: "0000000000" } // Default fallback
};

// API Endpoint: Submit Form
app.post('/api/visit', async (req, res) => {
    const { visitorName, visitorEmail, visitorPhone, organization, whomToMeet, date, purpose, numPeople } = req.body;

    try {
        const sql = `INSERT INTO visits (visitorName, visitorEmail, visitorPhone, organization, whomToMeet, date, purpose, numPeople) VALUES (?, ?, ?, ?, ?, ?, ?, ?)`;
        const [result] = await promisePool.query(sql, [
            visitorName,
            visitorEmail || '',
            visitorPhone || '',
            organization || '',
            whomToMeet,
            date,
            purpose,
            numPeople || 1
        ]);

        const visitId = result.insertId;
        const baseUrl = getBaseUrl();
        const visit = { visitorName, organization, whomToMeet, date, purpose, numPeople: numPeople || 1 };
        const approveLink = `${baseUrl}/response.html?id=${visitId}&action=approve`;
        const rejectLink = `${baseUrl}/response.html?id=${visitId}&action=reject`;
        const hostDetails = employeeDirectory[whomToMeet] || employeeDirectory['Other'];

        const messageHtml = buildEmployeeEmailHtml(visit, approveLink, rejectLink);
        const messageText = buildEmployeeWhatsAppText(visit, approveLink, rejectLink);

        if (process.env.EMAIL_USER && process.env.EMAIL_PASS) {
            transporter.sendMail({
                from: process.env.EMAIL_USER,
                to: hostDetails.email,
                subject: `New Visitor: ${visitorName}`,
                text: messageText,
                html: messageHtml
            }).then(() => {
                console.log(`Email sent to ${hostDetails.email}`);
            }).catch(err => {
                console.error('Error sending email:', err.message);
            });
        } else {
            console.log(`[Email skipped] Would send to ${hostDetails.email}`);
        }

        sendWhatsAppMessage(hostDetails.phone, messageText);
        console.log(`New visitor registered: ${visitorName} -> ${whomToMeet}`);

        res.json({ success: true, message: 'Registration submitted successfully.', id: visitId });
    } catch (err) {
        console.error('Error inserting visit:', err);
        res.status(500).json({ error: 'Database error' });
    }
});

// API Endpoint: Get single visit details (for response page)
app.get('/api/visit/:id', async (req, res) => {
    try {
        const [rows] = await promisePool.query('SELECT * FROM visits WHERE id = ?', [req.params.id]);
        if (rows.length === 0) return res.status(404).json({ error: 'Not found' });
        res.json({ visit: rows[0] });
    } catch (err) {
        res.status(500).json({ error: 'Database error' });
    }
});

// API Endpoint: Admin Approves or Rejects
app.post('/api/visit/:id/respond', async (req, res) => {
    const visitId = req.params.id;
    const { status, meetingTime, note } = req.body;

    try {
        const sql = `UPDATE visits SET status = ?, meetingTime = ?, note = ? WHERE id = ?`;
        await promisePool.query(sql, [status, meetingTime || null, note || null, visitId]);
        console.log(`Visit ${visitId} marked as ${status}`);
        res.json({ success: true, message: 'Response saved.' });
    } catch (err) {
        console.error('Error updating response:', err);
        res.status(500).json({ error: 'Database error' });
    }
});

// API Endpoint: Get all visits for Admin
app.get('/api/visits', async (req, res) => {
    try {
        const [rows] = await promisePool.query(`SELECT * FROM visits ORDER BY createdAt DESC`);
        res.json({ visits: rows });
    } catch (err) {
        console.error('Error fetching visits:', err);
        res.status(500).json({ error: 'Database error' });
    }
});

// API Endpoint: Download all visits as Excel-compatible CSV
app.get('/api/visits/export', async (req, res) => {
    try {
        const [rows] = await promisePool.query(`SELECT * FROM visits ORDER BY createdAt DESC`);
        const headers = ['ID', 'Visitor Name', 'Organization', 'Whom to Meet', 'Date', 'Purpose', 'People', 'Status', 'Meeting Time', 'Note', 'Submitted At'];
        const escape = (val) => {
            const s = val == null ? '' : String(val);
            return `"${s.replace(/"/g, '""')}"`;
        };
        const lines = [
            headers.join(','),
            ...rows.map(v => [
                v.id, v.visitorName, v.organization, v.whomToMeet, v.date, v.purpose,
                v.numPeople, v.status, v.meetingTime, v.note,
                v.createdAt ? new Date(v.createdAt).toISOString() : ''
            ].map(escape).join(','))
        ];
        const csv = '\uFEFF' + lines.join('\r\n');
        res.setHeader('Content-Type', 'text/csv; charset=utf-8');
        res.setHeader('Content-Disposition', `attachment; filename="visitor-records-${new Date().toISOString().slice(0, 10)}.csv"`);
        res.send(csv);
    } catch (err) {
        console.error('Error exporting visits:', err);
        res.status(500).json({ error: 'Export failed' });
    }
});

// API Endpoint: WhatsApp status (for QR page polling)
app.get('/api/whatsapp/status', async (req, res) => {
    let qrImage = null;
    if (latestQR) {
        try {
            qrImage = await QRCode.toDataURL(latestQR, { width: 300 });
        } catch (_) {}
    }
    res.json({ ready: waReady, qr: qrImage });
});

// WhatsApp QR page — always shows QR; success only for the person who just scanned
app.get('/whatsapp-qr', (req, res) => {
    res.send(`<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect WhatsApp</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', -apple-system, sans-serif;
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 2rem 1rem; background: #f3f4f6; color: #111827;
        }
        .card {
            width: 100%; max-width: 520px; background: #fff;
            border: 1px solid #e5e7eb; border-radius: 12px;
            padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.06); text-align: center;
        }
        h1 { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.4rem; }
        p { color: #6b7280; font-size: 0.9rem; line-height: 1.6; margin: 0.3rem 0; }
        .steps {
            text-align: left; margin: 1rem 0; background: #f9fafb;
            border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.1rem;
            list-style-type: none;
        }
        .steps li { margin: 0.35rem 0; font-size: 0.88rem; color: #374151; }
        img {
            border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px;
            background: #fff; margin: 1rem auto 0.5rem; display: block; max-width: 260px; width: 100%;
        }
        .success-box {
            background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px;
            padding: 1.5rem; margin-bottom: 1rem;
        }
        .success-box h2 { color: #065f46; font-size: 1.1rem; margin-bottom: 0.3rem; }
        .success-box p { color: #047857; font-size: 0.88rem; }
        .hidden { display: none; }
        .note { font-size: 0.82rem; color: #9ca3af; margin-top: 0.75rem; }
        .btn {
            display: inline-block; margin-top: 1rem; padding: 0.65rem 1.2rem;
            border-radius: 8px; font-weight: 600; font-size: 0.88rem;
            border: 1.5px solid #e5e7eb; background: #fff; color: #374151;
            cursor: pointer; text-decoration: none; font-family: inherit;
        }
        .btn:hover { background: #f9fafb; }
        .spinner { color: #6b7280; padding: 2rem 0; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="card">
        <div id="successView" class="hidden">
            <div class="success-box">
                <h2>WhatsApp Linked</h2>
                <p>Your WhatsApp is connected. You can close this page.</p>
            </div>
            <p class="note">This message is only shown to you. Other employees will see the QR code when they open this link.</p>
        </div>

        <div id="qrView" class="hidden">
            <h1>Connect WhatsApp</h1>
            <p>Open WhatsApp on your phone and scan the QR code below.</p>
            <ul class="steps">
                <li>1. Open WhatsApp on your phone</li>
                <li>2. Tap Linked Devices</li>
                <li>3. Tap Link a Device</li>
                <li>4. Scan the QR code below</li>
            </ul>
            <img id="qrImage" alt="WhatsApp QR code" />
            <p class="note">QR code refreshes automatically.</p>
        </div>

        <div id="loadingView">
            <p class="spinner">Preparing QR code...</p>
        </div>
    </div>

    <script>
        let wasWaiting = sessionStorage.getItem('wa_waiting') === '1';
        let showingSuccess = false;
        let reconnecting = false;

        async function poll() {
            if (showingSuccess) return;

            try {
                const res = await fetch('/api/whatsapp/status');
                const data = await res.json();

                if (data.ready && wasWaiting) {
                    showingSuccess = true;
                    sessionStorage.removeItem('wa_waiting');
                    document.getElementById('loadingView').classList.add('hidden');
                    document.getElementById('qrView').classList.add('hidden');
                    document.getElementById('successView').classList.remove('hidden');
                    return;
                }

                if (data.ready && !wasWaiting && !reconnecting) {
                    reconnecting = true;
                    document.getElementById('loadingView').classList.remove('hidden');
                    document.getElementById('qrView').classList.add('hidden');
                    document.getElementById('loadingView').querySelector('p').textContent = 'Preparing QR code...';
                    await fetch('/api/whatsapp/reconnect', { method: 'POST' });
                    setTimeout(poll, 2500);
                    return;
                }

                if (data.qr) {
                    reconnecting = false;
                    wasWaiting = true;
                    sessionStorage.setItem('wa_waiting', '1');
                    document.getElementById('loadingView').classList.add('hidden');
                    document.getElementById('successView').classList.add('hidden');
                    document.getElementById('qrView').classList.remove('hidden');
                    document.getElementById('qrImage').src = data.qr;
                } else if (!reconnecting) {
                    document.getElementById('loadingView').classList.remove('hidden');
                    document.getElementById('qrView').classList.add('hidden');
                }
            } catch (e) {}
        }

        poll();
        setInterval(poll, 3000);
    </script>
</body>
</html>`);
});

app.post('/api/whatsapp/reconnect', async (req, res) => {
    waReady = false;
    waInitializing = false;
    latestQR = null;
    const old = waClient;
    waClient = null;
    res.json({ ok: true });
    try { if (old) await old.destroy(); } catch (_) {}
    setTimeout(() => initWhatsApp(), 2000);
});

app.get('/whatsapp-logout', async (req, res) => {
    waReady = false;
    waInitializing = false;
    latestQR = null;
    const old = waClient;
    waClient = null;
    res.redirect('/whatsapp-qr');
    try { if (old) await old.destroy(); } catch (_) {}
    setTimeout(() => initWhatsApp(), 2000);
});

// Bridge API — Hostinger PHP calls this to send WhatsApp messages
app.post('/api/send-whatsapp', async (req, res) => {
    const { to, message } = req.body;
    if (!to || !message) {
        return res.status(400).json({ error: 'Missing to or message' });
    }
    await sendWhatsAppMessage(to, message);
    res.json({ success: true, ready: waReady });
});

app.listen(PORT, '0.0.0.0', () => {
    const base = getBaseUrl();
    console.log(`Server running on http://localhost:${PORT}`);
    console.log(`Network access:   ${base}`);
    console.log(`Visitor form:     ${base}/`);
    console.log(`Admin dashboard:  ${base}/admin.html  (QR Generator in sidebar)`);
    console.log(`WhatsApp setup:   ${base}/whatsapp-qr`);
});
