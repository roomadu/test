<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: admin.php');
    exit;
}

$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    if ($u === ADMIN_USER && $p === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    }
    $loginError = 'Incorrect username or password.';
}

if (empty($_SESSION['admin_logged_in'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; color: #0f172a; min-height: 100vh; }
        .login-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .login-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 2.5rem; width: 100%; max-width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .login-card h1 { font-size: 1.4rem; margin-bottom: 0.3rem; }
        .login-card p { color: #64748b; font-size: 0.88rem; margin-bottom: 1.5rem; }
        .field { margin-bottom: 1rem; }
        .field label { display: block; font-size: 0.83rem; font-weight: 600; margin-bottom: 0.35rem; color: #374151; }
        .field input { width: 100%; padding: 0.65rem 0.85rem; border: 1.5px solid #d1d5db; border-radius: 8px; font-family: inherit; font-size: 0.9rem; }
        .field input:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
        .error { background: #fef2f2; color: #991b1b; padding: 0.65rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 1rem; }
        .btn { padding: 0.65rem 1.2rem; border-radius: 8px; font-family: inherit; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s; }
        .btn-primary { background: #4f46e5; color: #fff; width: 100%; padding: 0.75rem; }
        .btn-primary:hover { background: #4338ca; }
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="login-card">
            <h1>Admin Login</h1>
            <p>Sign in to manage visitor records</p>
            <?php if ($loginError): ?>
                <div class="error"><?= htmlspecialchars($loginError) ?></div>
            <?php endif; ?>
            <form method="post" action="admin.php">
                <div class="field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Sign In</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php
exit;
}

$base = rtrim(BASE_URL, '/');
if ($base === '' || strpos($base, 'yourdomain.com') !== false) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $base = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}
$formUrl = $base . '/';
$whatsappConfigured = defined('WHATSAPP_SETUP_URL') && WHATSAPP_SETUP_URL
    && strpos(WHATSAPP_SETUP_URL, 'your-node-server') === false;
$whatsappUrl = $whatsappConfigured ? WHATSAPP_SETUP_URL : ($base . '/whatsapp-setup.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            min-height: 100vh;
        }

        .login-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .login-card h1 {
            font-size: 1.4rem;
            margin-bottom: 0.3rem;
        }

        .login-card p {
            color: #64748b;
            font-size: 0.88rem;
            margin-bottom: 1.5rem;
        }

        .field {
            margin-bottom: 1rem;
        }

        .field label {
            display: block;
            font-size: 0.83rem;
            font-weight: 600;
            margin-bottom: 0.35rem;
            color: #374151;
        }

        .field input {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.9rem;
        }

        .field input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .error {
            background: #fef2f2;
            color: #991b1b;
            padding: 0.65rem;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        .btn {
            padding: 0.65rem 1.2rem;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.15s;
        }

        .btn-primary {
            background: #4f46e5;
            color: #fff;
            width: 100%;
            padding: 0.75rem;
        }

        .btn-primary:hover {
            background: #4338ca;
        }

        .btn-outline {
            background: #fff;
            border: 1.5px solid #e2e8f0;
            color: #374151;
        }

        .btn-outline:hover {
            background: #f8fafc;
        }

        .btn-green {
            background: #059669;
            color: #fff;
        }

        .btn-green:hover {
            background: #047857;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 240px;
            background: #1e293b;
            color: #fff;
            padding: 1.5rem 0;
            flex-shrink: 0;
        }

        .sidebar-brand {
            padding: 0 1.25rem 1.5rem;
            border-bottom: 1px solid #334155;
            margin-bottom: 1rem;
        }

        .sidebar-brand h2 {
            font-size: 1rem;
            font-weight: 700;
        }

        .sidebar-brand p {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        .nav-item {
            display: block;
            padding: 0.75rem 1.25rem;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            cursor: pointer;
            border-left: 3px solid transparent;
            transition: all 0.15s;
        }

        .nav-item:hover {
            background: #334155;
            color: #fff;
        }

        .nav-item.active {
            background: #334155;
            color: #fff;
            border-left-color: #818cf8;
        }

        .sidebar-footer {
            padding: 1rem 1.25rem;
            margin-top: auto;
            position: absolute;
            bottom: 0;
            width: 240px;
        }

        .sidebar-footer a {
            color: #94a3b8;
            font-size: 0.82rem;
            text-decoration: none;
        }

        .main {
            flex: 1;
            padding: 1.5rem 2rem 3rem;
            overflow-x: hidden;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .page-header h1 {
            font-size: 1.4rem;
            font-weight: 700;
        }

        .page-header p {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 2px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.2rem;
        }

        .stat-label {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-top: 0.25rem;
        }

        .panel {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .panel-head {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .panel-body {
            padding: 1.25rem;
        }

        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            align-items: center;
            background: #fafbfc;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .filter-group label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #94a3b8;
        }

        .filter-select,
        .filter-search {
            padding: 0.5rem 0.75rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.84rem;
            background: #fff;
            min-width: 140px;
        }

        .filter-search {
            min-width: 200px;
            flex: 1;
        }

        .view-toggle {
            display: flex;
            gap: 0.3rem;
            margin-left: auto;
        }

        .view-btn {
            padding: 0.45rem 0.85rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid #e2e8f0;
            background: #fff;
            font-family: inherit;
            color: #64748b;
        }

        .view-btn.active {
            background: #1e3a8a;
            color: #fff;
            border-color: #1e3a8a;
        }

        .group-section {
            margin-bottom: 1.5rem;
        }

        .group-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background: #1e3a8a;
            color: #fff;
            border-radius: 8px 8px 0 0;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .group-header span {
            font-size: 0.78rem;
            opacity: 0.85;
            font-weight: 500;
        }

        .group-section .panel {
            border-radius: 0 0 8px 8px;
            margin-bottom: 0;
        }

        .group-section table {
            margin: 0;
        }

        .period-tabs {
            display: flex;
            gap: 0.3rem;
            flex-wrap: wrap;
        }

        .period-tab {
            padding: 0.4rem 0.75rem;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            font-family: inherit;
        }

        .period-tab.active {
            background: #dbeafe;
            color: #1d4ed8;
            border-color: #93c5fd;
        }

        .toolbar {
            display: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.84rem;
        }

        th {
            text-align: left;
            padding: 0.7rem 1rem;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #94a3b8;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        tr:hover td {
            background: #fafbfc;
        }

        .badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 100px;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        .qr-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .qr-box {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
        }

        .qr-box h3 {
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
        }

        .qr-box p {
            font-size: 0.82rem;
            color: #64748b;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .qr-output {
            display: flex;
            justify-content: center;
            margin: 1rem 0;
            min-height: 220px;
            align-items: center;
        }

        .qr-output img,
        .qr-output canvas {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px;
        }

        .link-row {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }

        .link-input {
            flex: 1;
            padding: 0.5rem 0.75rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.8rem;
            font-family: inherit;
            color: #64748b;
        }

        .preset-btns {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .preset-btn {
            padding: 0.45rem 0.85rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            font-family: inherit;
        }

        .preset-btn:hover {
            border-color: #4f46e5;
            color: #4f46e5;
        }

        .links-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .link-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }

        .link-card-info {
            flex: 1;
        }

        .link-card-info strong {
            display: block;
            font-size: 0.9rem;
            margin-bottom: 2px;
        }

        .link-card-info span {
            font-size: 0.8rem;
            color: #64748b;
            word-break: break-all;
        }

        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 1rem 1.2rem;
            font-size: 0.85rem;
            color: #1e40af;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        @media (max-width: 900px) {
            .layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                position: relative;
            }

            .sidebar-footer {
                position: static;
                width: 100%;
            }

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .qr-grid {
                grid-template-columns: 1fr;
            }

            .main {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>

    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h2>Visitor System</h2>
                <p>Admin Dashboard</p>
            </div>
            <nav>
                <a class="nav-item active" data-section="records">Visitor Records</a>
                <a class="nav-item" data-section="qr">WhatsApp QR</a>
            </nav>
            <div class="sidebar-footer">
                <a href="?logout=1">Sign Out</a>
            </div>
        </aside>

        <main class="main">
            <?php if (!isDbConfigured()): ?>
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:1rem 1.25rem;border-radius:10px;margin-bottom:1.25rem;font-size:0.9rem;line-height:1.6">
                <strong>⚠ Setup needed:</strong> The database is not connected yet, so visitor records can't be shown.
                Open <code>includes/config.php</code> on your hosting file manager and fill in your real
                <code>DB_NAME</code>, <code>DB_USER</code>, <code>DB_PASS</code> and <code>DB_HOST</code>
                (create them first under Hostinger hPanel → Databases), then reload this page.
            </div>
            <?php endif; ?>
            <!-- RECORDS -->
            <div class="section active" id="sec-records">
                <div class="page-header">
                    <div>
                        <h1>Visitor Records</h1>
                        <p>Filter by period, person, or status</p>
                    </div>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap">
                        <button class="btn btn-outline" onclick="goToQr()">WhatsApp QR</button>
                        <button class="btn btn-green" onclick="window.location='api/export.php'">Download Excel</button>
                    </div>
                </div>

                <div class="stats">
                    <div class="stat">
                        <div class="stat-label">Total</div>
                        <div class="stat-value" id="sTotal">-</div>
                    </div>
                    <div class="stat">
                        <div class="stat-label">Pending</div>
                        <div class="stat-value" id="sPending" style="color:#d97706">-</div>
                    </div>
                    <div class="stat">
                        <div class="stat-label">Approved</div>
                        <div class="stat-value" id="sApproved" style="color:#059669">-</div>
                    </div>
                    <div class="stat">
                        <div class="stat-label">Declined</div>
                        <div class="stat-value" id="sRejected" style="color:#dc2626">-</div>
                    </div>
                </div>

                <div class="panel">
                    <div class="filter-bar">
                        <div class="filter-group">
                            <label>Period</label>
                            <div class="period-tabs">
                                <button class="period-tab active" onclick="setPeriod('all',this)">All</button>
                                <button class="period-tab" onclick="setPeriod('month',this)">This Month</button>
                            </div>
                        </div>
                        <div class="filter-group">
                            <label>Person to Meet</label>
                            <select class="filter-select" id="personFilter" onchange="renderView()">
                                <option value="all">All People</option>
                                <option value="Marc Perera (Chief Digital Officer)">Marc Perera</option>
                                <option value="Hashantha Hemachandra (General Manager (Designate) - Group Digital)">
                                    Hashantha Hemachandra</option>
                                <option value="Ishara Nanayakkarawasam (Director - Digital Content)">Ishara
                                    Nanayakkarawasam</option>
                                <option value="Chamara Silva (Performance Marketing Manager)">Chamara Silva</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Status</label>
                            <select class="filter-select" id="statusFilter" onchange="renderView()">
                                <option value="all">All Status</option>
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Declined</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Sort By</label>
                            <select class="filter-select" id="sortBy" onchange="renderView()">
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="name">Name A–Z</option>
                                <option value="person">Person to Meet</option>
                                <option value="date">Visit Date</option>
                            </select>
                        </div>
                        <div class="filter-group" style="flex:1;min-width:180px">
                            <label>Search</label>
                            <input type="text" class="filter-search" id="searchInput"
                                placeholder="Search name, org, purpose..." oninput="renderView()">
                        </div>
                        <div class="view-toggle">
                            <button class="view-btn active" id="viewList" onclick="setViewMode('list')">List</button>
                            <button class="view-btn" id="viewGroup" onclick="setViewMode('group')">By Person</button>
                        </div>
                    </div>

                    <div id="listView" style="overflow-x:auto">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Organization</th>
                                    <th>Whom to Meet</th>
                                    <th>Date</th>
                                    <th>Purpose</th>
                                    <th>People</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                </tr>
                            </thead>
                            <tbody id="tbody">
                                <tr>
                                    <td colspan="9" style="text-align:center;padding:2rem;color:#94a3b8">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="groupView" class="hidden" style="padding:1rem 1.25rem"></div>
                </div>
            </div>

            <!-- WHATSAPP QR -->
            <div class="section" id="sec-qr">
                <div class="page-header">
                    <div>
                        <h1>WhatsApp Login QR</h1>
                        <p>Generate a QR code for employees to connect their WhatsApp</p>
                    </div>
                </div>

                <div class="info-box">
                    Give this QR to each employee. They scan it, open the setup page, then link their WhatsApp via
                    <strong>Linked Devices → Link a Device</strong>.
                </div>

                <?php if (!$whatsappConfigured): ?>
                <div style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;padding:1rem 1.25rem;border-radius:10px;margin-bottom:1.25rem;font-size:0.875rem;line-height:1.6">
                    <strong>Not linked to a WhatsApp bridge yet.</strong> Shared hosting can't run WhatsApp Web directly,
                    so this QR currently points to a setup page on your own site. To send real WhatsApp messages,
                    run the Node.js WhatsApp bridge (the same one you tested locally) on a server that stays online
                    24/7, then set <code>WHATSAPP_SETUP_URL</code> and <code>WHATSAPP_BRIDGE_URL</code> in
                    <code>includes/config.php</code> to point to it. Until then, visitor notifications are still sent by email.
                </div>
                <?php endif; ?>

                <div class="panel">
                    <div class="panel-body" style="max-width:420px;margin:0 auto;text-align:center">
                        <h3 style="font-size:1rem;margin-bottom:0.5rem">WhatsApp Setup QR</h3>
                        <p style="font-size:0.875rem;color:#64748b;margin-bottom:1.25rem;line-height:1.5">
                            Download or copy the link and share with the employee
                        </p>
                        <div class="qr-output" id="waQrCanvas"></div>
                        <div class="link-row">
                            <input type="text" class="link-input" id="waLinkDisplay" readonly
                                value="<?= htmlspecialchars($whatsappUrl) ?>">
                            <button class="btn btn-outline" onclick="copyWaLink()">Copy Link</button>
                        </div>
                        <button class="btn btn-green" style="margin-top:1rem" onclick="downloadWaQr()">Download QR
                            (PNG)</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Navigation
        function goToQr() {
            document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            document.querySelector('[data-section="qr"]').classList.add('active');
            document.getElementById('sec-qr').classList.add('active');
            window.scrollTo(0, 0);
        }

        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', () => {
                document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
                document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
                item.classList.add('active');
                document.getElementById('sec-' + item.dataset.section).classList.add('active');
            });
        });

        // Records
        let allVisits = [];
        let currentPeriod = 'all';
        let viewMode = 'list';

        function badgeClass(s) {
            if (s === 'Approved') return 'badge-approved';
            if (s === 'Rejected') return 'badge-rejected';
            return 'badge-pending';
        }

        function fmtDate(d) {
            if (!d) return '-';
            return new Date(d).toLocaleString('en-LK', { dateStyle: 'medium', timeStyle: 'short' });
        }

        function shortPerson(name) {
            return name.split('(')[0].trim();
        }

        function setPeriod(p, btn) {
            currentPeriod = p;
            document.querySelectorAll('.period-tab').forEach(t => t.classList.remove('active'));
            btn.classList.add('active');
            renderView();
        }

        function setViewMode(mode) {
            viewMode = mode;
            document.getElementById('viewList').classList.toggle('active', mode === 'list');
            document.getElementById('viewGroup').classList.toggle('active', mode === 'group');
            document.getElementById('listView').classList.toggle('hidden', mode === 'group');
            document.getElementById('groupView').classList.toggle('hidden', mode === 'list');
            renderView();
        }

        function inPeriod(visit) {
            if (currentPeriod === 'all') return true;
            const d = new Date(visit.createdAt || visit.date);
            const now = new Date();
            if (currentPeriod === 'month') {
                return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
            }
            return true;
        }

        function getFiltered() {
            const q = document.getElementById('searchInput').value.toLowerCase().trim();
            const person = document.getElementById('personFilter').value;
            const status = document.getElementById('statusFilter').value;
            const sort = document.getElementById('sortBy').value;

            let list = allVisits.filter(v => {
                if (!inPeriod(v)) return false;
                if (person !== 'all' && v.whomToMeet !== person) return false;
                if (status !== 'all' && v.status !== status) return false;
                if (q) {
                    const hay = [v.visitorName, v.organization, v.whomToMeet, v.purpose, v.date].join(' ').toLowerCase();
                    if (!hay.includes(q)) return false;
                }
                return true;
            });

            list.sort((a, b) => {
                if (sort === 'newest') return new Date(b.createdAt) - new Date(a.createdAt);
                if (sort === 'oldest') return new Date(a.createdAt) - new Date(b.createdAt);
                if (sort === 'name') return (a.visitorName || '').localeCompare(b.visitorName || '');
                if (sort === 'person') return (a.whomToMeet || '').localeCompare(b.whomToMeet || '');
                if (sort === 'date') return (a.date || '').localeCompare(b.date || '');
                return 0;
            });

            return list;
        }

        function rowHtml(v) {
            return `<tr>
            <td>${v.id}</td>
            <td><strong>${v.visitorName}</strong></td>
            <td>${v.organization || '-'}</td>
            <td>${shortPerson(v.whomToMeet)}</td>
            <td>${v.date}</td>
            <td>${v.purpose}</td>
            <td>${v.numPeople || 1}</td>
            <td><span class="badge ${badgeClass(v.status)}">${v.status}</span></td>
            <td>${fmtDate(v.createdAt)}</td>
        </tr>`;
        }

        function renderTable() {
            const tbody = document.getElementById('tbody');
            const filtered = getFiltered();
            if (!filtered.length) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:2rem;color:#94a3b8">No records found.</td></tr>';
                return;
            }
            tbody.innerHTML = filtered.map(rowHtml).join('');
        }

        function renderGrouped() {
            const container = document.getElementById('groupView');
            const filtered = getFiltered();
            if (!filtered.length) {
                container.innerHTML = '<p style="text-align:center;padding:2rem;color:#94a3b8">No records found.</p>';
                return;
            }

            const groups = {};
            filtered.forEach(v => {
                const key = v.whomToMeet || 'Unknown';
                if (!groups[key]) groups[key] = [];
                groups[key].push(v);
            });

            container.innerHTML = Object.keys(groups).sort().map(person => {
                const rows = groups[person];
                const pending = rows.filter(r => r.status === 'Pending').length;
                return `<div class="group-section">
                <div class="group-header">
                    <div>${shortPerson(person)}</div>
                    <span>${rows.length} visitor${rows.length !== 1 ? 's' : ''}${pending ? ' · ' + pending + ' pending' : ''}</span>
                </div>
                <div class="panel" style="border-radius:0 0 8px 8px;border-top:none">
                    <table>
                        <thead><tr>
                            <th>#</th><th>Name</th><th>Organization</th><th>Date</th>
                            <th>Purpose</th><th>People</th><th>Status</th><th>Submitted</th>
                        </tr></thead>
                        <tbody>${rows.map(v => `<tr>
                            <td>${v.id}</td><td><strong>${v.visitorName}</strong></td>
                            <td>${v.organization || '-'}</td><td>${v.date}</td>
                            <td>${v.purpose}</td><td>${v.numPeople || 1}</td>
                            <td><span class="badge ${badgeClass(v.status)}">${v.status}</span></td>
                            <td>${fmtDate(v.createdAt)}</td>
                        </tr>`).join('')}</tbody>
                    </table>
                </div>
            </div>`;
            }).join('');
        }

        function renderView() {
            updateStats();
            if (viewMode === 'group') renderGrouped();
            else renderTable();
        }

        function updateStats() {
            const filtered = getFiltered();
            document.getElementById('sTotal').textContent = filtered.length;
            document.getElementById('sPending').textContent = filtered.filter(v => v.status === 'Pending').length;
            document.getElementById('sApproved').textContent = filtered.filter(v => v.status === 'Approved').length;
            document.getElementById('sRejected').textContent = filtered.filter(v => v.status === 'Rejected').length;
        }

        async function loadVisits() {
            let data = null;
            let rawText = '';
            try {
                const res = await fetch('api/visits.php', { credentials: 'same-origin' });
                rawText = await res.text();
                data = JSON.parse(rawText);
            } catch (e) {
                const detail = rawText ? rawText.slice(0, 200).replace(/</g, '&lt;') : e.message;
                document.getElementById('tbody').innerHTML =
                    '<tr><td colspan="9" style="text-align:center;padding:2rem;color:#dc2626">Failed to load records.<br><span style="font-size:0.75rem;color:#94a3b8">' + detail + '</span></td></tr>';
                return;
            }

            if (data.error) {
                document.getElementById('tbody').innerHTML =
                    '<tr><td colspan="9" style="text-align:center;padding:2rem;color:#dc2626">' + data.error + '</td></tr>';
                return;
            }

            allVisits = data.visits || [];
            renderView();
        }

        loadVisits();

        // WhatsApp QR only
        function renderQr(containerId, url) {
            const el = document.getElementById(containerId);
            el.innerHTML = '';
            return new QRCode(el, { text: url, width: 220, height: 220, colorDark: '#000', colorLight: '#fff', correctLevel: QRCode.CorrectLevel.M });
        }

        function downloadWaQr() {
            const canvas = document.querySelector('#waQrCanvas canvas');
            if (!canvas) return;
            const a = document.createElement('a');
            a.download = 'whatsapp-login-qr.png';
            a.href = canvas.toDataURL('image/png');
            a.click();
        }

        function copyWaLink() {
            navigator.clipboard.writeText(document.getElementById('waLinkDisplay').value);
            alert('Link copied');
        }

        renderQr('waQrCanvas', '<?= addslashes($whatsappUrl) ?>');
    </script>
</body>

</html>