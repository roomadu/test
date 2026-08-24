<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host Response</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background: #f3f4f6;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 2.5rem 1rem 4rem;
            color: #111;
        }

        .card {
            width: 100%;
            max-width: 620px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08), 0 4px 20px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .card-accent {
            height: 5px;
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
        }

        .card-body { padding: 2.5rem 2.8rem 3rem; }

        .form-header {
            margin-bottom: 1.8rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.3rem;
        }

        .form-header p { font-size: 0.87rem; color: #6b7280; }

        /* Visitor info grid */
        .visitor-info {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 1.3rem 1.5rem;
            margin-bottom: 1.8rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem 1.5rem;
        }

        .info-item label {
            display: block;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #9ca3af;
            margin-bottom: 2px;
        }

        .info-item span {
            font-size: 0.88rem;
            color: #111827;
            font-weight: 500;
        }

        .info-item.full { grid-column: 1 / -1; }

        /* Action buttons */
        .action-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .btn-action {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: 1.5px solid;
            position: relative;
            overflow: hidden;
        }

        .btn-action::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 60%);
            pointer-events: none;
        }

        .btn-approve {
            background: #059669;
            border-color: #059669;
            color: #fff;
            box-shadow: 0 2px 8px rgba(5,150,105,0.25);
        }

        .btn-approve:hover { background: #047857; box-shadow: 0 4px 14px rgba(5,150,105,0.35); transform: translateY(-1px); }
        .btn-approve.active { background: #047857; box-shadow: 0 4px 14px rgba(5,150,105,0.4); }

        .btn-reject {
            background: #dc2626;
            border-color: #dc2626;
            color: #fff;
            box-shadow: 0 2px 8px rgba(220,38,38,0.25);
        }

        .btn-reject:hover { background: #b91c1c; box-shadow: 0 4px 14px rgba(220,38,38,0.35); transform: translateY(-1px); }
        .btn-reject.active { background: #b91c1c; box-shadow: 0 4px 14px rgba(220,38,38,0.4); }

        .btn-action.dimmed { opacity: 0.35; }

        /* Form */
        .form-group { margin-bottom: 1.2rem; }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.4rem;
        }

        input[type="datetime-local"], textarea {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.88rem;
            color: #111827;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        input:focus, textarea:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }

        textarea { min-height: 88px; resize: vertical; }
        input::placeholder, textarea::placeholder { color: #9ca3af; }

        .btn-submit {
            width: 100%;
            padding: 0.85rem;
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s, transform 0.15s;
            position: relative;
            overflow: hidden;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.12) 0%, transparent 60%);
            pointer-events: none;
        }

        .btn-submit:hover { background: #4338ca; box-shadow: 0 4px 14px rgba(79,70,229,0.4); transform: translateY(-1px); }
        .btn-submit:disabled { opacity: 0.65; cursor: not-allowed; transform: none; }

        /* Success */
        #successMessage { text-align: center; padding: 3.5rem 2rem; }

        .success-circle {
            width: 72px; height: 72px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            animation: popIn 0.4s ease;
        }

        .success-circle.green { background: #ecfdf5; }
        .success-circle.red { background: #fef2f2; }

        @keyframes popIn {
            0% { transform: scale(0.5); opacity: 0; }
            80% { transform: scale(1.08); }
            100% { transform: scale(1); opacity: 1; }
        }

        #successMessage h2 { font-size: 1.4rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem; }
        #successMessage p { font-size: 0.88rem; color: #6b7280; line-height: 1.6; }

        .hidden { display: none !important; }

        @media (max-width: 480px) {
            .card-body { padding: 2rem 1.5rem 2.5rem; }
            .visitor-info { grid-template-columns: 1fr; }
            .info-item.full { grid-column: 1; }
            .action-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-accent"></div>
        <div class="card-body">
            <div class="form-header">
                <h1>Visitor Response</h1>
                <p>Please review the visitor request and approve or decline.</p>
            </div>

            <div id="visitorDetails" class="visitor-info hidden">
                <div class="info-item">
                    <label>Visitor Name</label>
                    <span id="infoName">—</span>
                </div>
                <div class="info-item">
                    <label>Organization</label>
                    <span id="infoOrg">—</span>
                </div>
                <div class="info-item">
                    <label>Date</label>
                    <span id="infoDate">—</span>
                </div>
                <div class="info-item">
                    <label>No. of People</label>
                    <span id="infoNum">—</span>
                </div>
                <div class="info-item full">
                    <label>Purpose</label>
                    <span id="infoPurpose">—</span>
                </div>
            </div>

            <div class="action-row" id="actionButtons">
                <button class="btn-action btn-approve" id="btnApprove">Approve</button>
                <button class="btn-action btn-reject" id="btnReject">Decline</button>
            </div>

            <form id="responseForm" class="hidden">
                <input type="hidden" id="status" value="">

                <div class="form-group hidden" id="noteGroup">
                    <label for="note">Reason (optional)</label>
                    <textarea id="note" name="note" placeholder="E.g., I am unavailable today."></textarea>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">Confirm</button>
            </form>

            <div id="successMessage" class="hidden">
                <div class="success-circle green" id="successCircle">OK</div>
                <h2 id="successTitle">Response Saved</h2>
                <p id="successBody">The record has been updated in the admin dashboard.</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const params = new URLSearchParams(window.location.search);
            const visitId = params.get('id');
            const action = params.get('action');

            if (!visitId) { alert('Invalid link: Missing visitor ID.'); return; }

            try {
                const res = await fetch(`api/visit-get.php?id=${visitId}`);
                if (res.ok) {
                    const { visit: v } = await res.json();
                    if (v) {
                        document.getElementById('infoName').textContent = v.visitorName || '—';
                        document.getElementById('infoOrg').textContent = v.organization || '—';
                        document.getElementById('infoDate').textContent = v.date || '—';
                        document.getElementById('infoNum').textContent = v.numPeople || '—';
                        document.getElementById('infoPurpose').textContent = v.purpose || '—';
                        document.getElementById('visitorDetails').classList.remove('hidden');
                    }
                }
            } catch (_) {}

            const btnApprove = document.getElementById('btnApprove');
            const btnReject = document.getElementById('btnReject');
            const form = document.getElementById('responseForm');
            const statusInput = document.getElementById('status');
            const noteGroup = document.getElementById('noteGroup');

            const setAction = (act) => {
                form.classList.remove('hidden');
                if (act === 'approve') {
                    statusInput.value = 'Approved';
                    noteGroup.classList.add('hidden');
                    btnApprove.classList.add('active');
                    btnApprove.classList.remove('dimmed');
                    btnReject.classList.remove('active');
                    btnReject.classList.add('dimmed');
                } else {
                    statusInput.value = 'Rejected';
                    noteGroup.classList.remove('hidden');
                    btnReject.classList.add('active');
                    btnReject.classList.remove('dimmed');
                    btnApprove.classList.remove('active');
                    btnApprove.classList.add('dimmed');
                }
            };

            btnApprove.addEventListener('click', () => setAction('approve'));
            btnReject.addEventListener('click', () => setAction('reject'));
            if (action) setAction(action);

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = document.getElementById('submitBtn');
                btn.disabled = true;
                btn.textContent = 'Sending...';

                try {
                    const res = await fetch(`api/respond.php?id=${visitId}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            status: statusInput.value,
                            note: document.getElementById('note').value
                        })
                    });
                    const result = await res.json();
                    if (result.success) {
                        document.getElementById('actionButtons').classList.add('hidden');
                        form.classList.add('hidden');
                        const approved = statusInput.value === 'Approved';
                        const circle = document.getElementById('successCircle');
                        circle.textContent = approved ? 'OK' : 'X';
                        circle.className = 'success-circle ' + (approved ? 'green' : 'red');
                        document.getElementById('successTitle').textContent = approved ? 'Visit Approved' : 'Visit Declined';
                        document.getElementById('successBody').textContent = 'The record has been updated in the admin dashboard.';
                        document.getElementById('successMessage').classList.remove('hidden');
                    } else {
                        alert('Error saving response.');
                        btn.disabled = false;
                        btn.textContent = 'Send Response';
                    }
                } catch (_) {
                    alert('Connection error. Please try again.');
                    btn.disabled = false;
                    btn.textContent = 'Send Response';
                }
            });
        });
    </script>
</body>
</html>
