<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Form - Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 16px;
            min-height: 100vh;
            color: #242424;
            background: url('office_bg.jpg') center/cover no-repeat fixed;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(5, 20, 40, 0.55);
            z-index: 0;
        }

        .page {
            position: relative;
            z-index: 1;
            max-width: 700px;
            margin: 0 auto;
            padding: 2.5rem 1rem 4rem;
        }

        .form-card {
            background: rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border-radius: 4px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.35), 0 1px 4px rgba(0, 0, 0, 0.18);
            overflow: hidden;
        }

        /* ── Coloured top accent bar ── */
        .form-card::before {
            content: '';
            display: block;
            height: 6px;
            background: linear-gradient(90deg, #0d9488 0%, #2563eb 50%, #7c3aed 100%);
        }

        /* ── Header section – distinct teal-tinted background ── */
        .form-header {
            padding: 2rem 2.5rem 1.75rem;
            background: linear-gradient(135deg, rgba(15, 61, 62, 0.55) 0%, rgba(13, 75, 110, 0.55) 100%);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border-bottom: 3px solid #0d9488;
        }

        .form-header h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.25;
            letter-spacing: -0.01em;
        }

        .form-header .desc {
            margin-top: 0.85rem;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.85);
            line-height: 1.65;
        }

        .form-header .privacy {
            margin-top: 0.75rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.65;
        }

        /* ── Form body – clean white ── */
        .required-note {
            padding: 0.75rem 2.5rem 0;
            font-size: 0.875rem;
            color: #bc2f32;
            background: rgba(250, 250, 250, 0.45);
        }

        .question {
            padding: 1.75rem 2.5rem;
            border-top: 1px solid rgba(232, 232, 232, 0.6);
            background: rgba(255, 255, 255, 0.45);
        }

        .question-label {
            font-size: 1.0625rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 1.125rem;
            line-height: 1.4;
        }

        .question-label .req {
            color: #bc2f32;
        }

        .question-label .optional {
            font-weight: 400;
            color: #616161;
            font-size: 0.9375rem;
        }

        .field-line {
            width: 100%;
            border: none;
            border-bottom: 2px solid #d1d5db;
            padding: 0.6rem 0 0.6rem;
            font-family: inherit;
            font-size: 1rem;
            color: #1a1a1a;
            background: transparent;
            outline: none;
            transition: border-color 0.2s;
        }

        .field-line::placeholder {
            color: #9ca3af;
        }

        .field-line:focus {
            border-bottom: 2px solid #0d9488;
        }

        .choice-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .choice-item {
            display: flex;
            align-items: flex-start;
            gap: 0.875rem;
            padding: 0.75rem 0.875rem;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.12s, border-color 0.12s;
        }

        .choice-item:hover {
            background: #f0fdfc;
            border-color: #0d9488;
        }

        .choice-item input[type="radio"] {
            width: 18px;
            height: 18px;
            margin-top: 3px;
            flex-shrink: 0;
            accent-color: #0d9488;
            cursor: pointer;
        }

        .choice-text-wrap {
            flex: 1;
        }

        .choice-name {
            display: block;
            font-size: 1rem;
            color: #1a1a1a;
            line-height: 1.35;
            font-weight: 500;
        }

        .choice-role {
            display: block;
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 2px;
        }

        .form-actions {
            padding: 1.5rem 2.5rem 2rem;
            border-top: 1px solid #e8e8e8;
            background: #f9fafb;
        }

        .btn-submit {
            background: linear-gradient(90deg, #0d9488, #2563eb);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 0.75rem 2rem;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: 0.02em;
            transition: opacity 0.2s, transform 0.15s;
        }

        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .success-panel {
            padding: 3.5rem 2.5rem;
            text-align: center;
        }

        .success-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1.25rem;
            border-radius: 50%;
            background: #dff6dd;
            color: #107c10;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            font-weight: 700;
        }

        .success-panel h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #242424;
            margin-bottom: 0.5rem;
        }

        .success-panel p {
            font-size: 1rem;
            color: #616161;
        }

        .hidden {
            display: none !important;
        }

        @media (max-width: 600px) {
            .page {
                padding: 1rem 0.5rem 3rem;
            }

            .form-header {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }

            .question,
            .form-actions,
            .required-note {
                padding-left: 1.25rem;
                padding-right: 1.25rem;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="form-card">
            <div class="form-header">
                <h1>Visitor Form – Digital</h1>
                <p class="desc">For visitor registration and security purposes. Please provide the required details upon
                    arrival and departure.</p>
                <p class="privacy">When you submit this form, it will not automatically collect your details like name
                    and email address unless you provide it yourself.</p>
            </div>

            <div id="formContent">
                <p class="required-note"><span class="req">*</span> Required</p>

                <form id="visitorForm">
                    <div class="question">
                        <div class="question-label">1. Visitor Name <span class="req">*</span></div>
                        <input type="text" id="visitorName" class="field-line" placeholder="Enter your answer" required>
                    </div>

                    <div class="question">
                        <div class="question-label">2. Organization <span class="optional"></span></div>
                        <input type="text" id="organization" class="field-line" placeholder="Enter your answer">
                    </div>

                    <div class="question">
                        <div class="question-label">3. Whom to Meet / Department <span class="req">*</span></div>
                        <div class="choice-list">
                            <label class="choice-item">
                                <input type="radio" name="whomToMeet" value="Marc Perera (Chief Digital Officer)"
                                    required onchange="onPersonChange()">
                                <span class="choice-text-wrap">
                                    <span class="choice-name">Marc Perera</span>
                                    <span class="choice-role">Chief Digital Officer</span>
                                </span>
                            </label>
                            <label class="choice-item">
                                <input type="radio" name="whomToMeet"
                                    value="Hashantha Hemachandra (General Manager (Designate) - Group Digital)"
                                    onchange="onPersonChange()">
                                <span class="choice-text-wrap">
                                    <span class="choice-name">Hashantha Hemachandra</span>
                                    <span class="choice-role">GM – Group Digital</span>
                                </span>
                            </label>
                            <label class="choice-item">
                                <input type="radio" name="whomToMeet"
                                    value="Ishara Nanayakkarawasam (Director - Digital Content)"
                                    onchange="onPersonChange()">
                                <span class="choice-text-wrap">
                                    <span class="choice-name">Ishara Nanayakkarawasam</span>
                                    <span class="choice-role">Director – Digital Content</span>
                                </span>
                            </label>
                            <label class="choice-item">
                                <input type="radio" name="whomToMeet"
                                    value="Chamara Silva (Performance Marketing Manager)" onchange="onPersonChange()">
                                <span class="choice-text-wrap">
                                    <span class="choice-name">Chamara Silva</span>
                                    <span class="choice-role">Performance Marketing</span>
                                </span>
                            </label>
                            <label class="choice-item">
                                <input type="radio" name="whomToMeet" value="Other" onchange="onPersonChange()">
                                <span class="choice-text-wrap">
                                    <span class="choice-name">Other</span>
                                </span>
                            </label>
                        </div>
                        <div class="hidden" id="otherWrap" style="margin-top:1rem">
                            <input type="text" id="whomToMeetOther" class="field-line"
                                placeholder="Specify whom you are visiting">
                        </div>
                    </div>

                    <div class="question">
                        <div class="question-label">4. Date <span class="req">*</span></div>
                        <input type="date" id="date" class="field-line" required>
                    </div>

                    <div class="question">
                        <div class="question-label">5. Purpose <span class="req">*</span></div>
                        <input type="text" id="purpose" class="field-line" placeholder="Enter your answer" required>
                    </div>

                    <div class="question">
                        <div class="question-label">6. Number of People</div>
                        <input type="number" id="numPeople" class="field-line" min="1" value="1">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit" id="submitBtn">Submit</button>
                    </div>
                </form>
            </div>

            <div id="successMessage" class="success-panel hidden">
                <div class="success-icon">&#10003;</div>
                <h2>Your response was submitted</h2>
                <p>Thank you. You may proceed.</p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('date').valueAsDate = new Date();

        function onPersonChange() {
            const selected = document.querySelector('input[name="whomToMeet"]:checked');
            const otherWrap = document.getElementById('otherWrap');
            const otherInput = document.getElementById('whomToMeetOther');
            if (selected && selected.value === 'Other') {
                otherWrap.classList.remove('hidden');
                otherInput.required = true;
            } else {
                otherWrap.classList.add('hidden');
                otherInput.required = false;
                otherInput.value = '';
            }
        }

        document.getElementById('visitorForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.textContent = 'Submitting...';

            let whomToMeet = document.querySelector('input[name="whomToMeet"]:checked')?.value;
            if (whomToMeet === 'Other') whomToMeet = document.getElementById('whomToMeetOther').value;

            try {
                const res = await fetch('api/visit.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        visitorName: document.getElementById('visitorName').value,
                        organization: document.getElementById('organization').value,
                        whomToMeet,
                        date: document.getElementById('date').value,
                        purpose: document.getElementById('purpose').value,
                        numPeople: document.getElementById('numPeople').value,
                    })
                });
                const result = await res.json();
                if (result.success) {
                    document.getElementById('formContent').classList.add('hidden');
                    document.getElementById('successMessage').classList.remove('hidden');
                } else {
                    alert('Error submitting. Please try again.');
                    btn.disabled = false;
                    btn.textContent = 'Submit';
                }
            } catch {
                alert('Connection error. Please try again.');
                btn.disabled = false;
                btn.textContent = 'Submit';
            }
        });
    </script>
</body>

</html>
