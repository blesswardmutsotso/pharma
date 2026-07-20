<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FiscTech – Privacy Policy</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:      #f0faf4;
            --panel:   #ffffff;
            --border:  #d1fae5;
            --border2: #a7f3d0;
            --accent:  #16a34a;
            --accent2: #22c55e;
            --accent3: #4ade80;
            --dark:    #052e16;
            --dark2:   #14532d;
            --text:    #1a2e1f;
            --muted:   #6b7280;
            --light:   #374151;
            --white:   #ffffff;
            --success: #16a34a;
        }

        html, body {
            font-family: 'Sora', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, rgba(22,163,74,0.1) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
            z-index: 0;
        }

        .page-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* ════════════════════════════
           HEADER
        ════════════════════════════ */
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            height: 64px;
            background: rgba(255,255,255,0.94);
            border-bottom: 1.5px solid var(--border2);
            backdrop-filter: blur(16px);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 16px rgba(22,163,74,0.08);
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .header-logo-box {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--accent), #15803d);
            border-radius: 9px;
            display: grid;
            place-items: center;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            font-size: 13px;
            color: #fff;
            box-shadow: 0 3px 10px rgba(22,163,74,0.35);
        }

        .header-name {
            font-size: 19px;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.5px;
        }
        .header-name span { color: var(--accent); }

        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-tagline {
            font-size: 11px;
            font-weight: 500;
            color: var(--muted);
            font-family: 'JetBrains Mono', monospace;
            border: 1.5px solid var(--border2);
            padding: 3px 12px;
            border-radius: 20px;
            background: rgba(22,163,74,0.05);
        }

        .live-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: var(--success);
            display: inline-block;
            animation: pulse 2s infinite;
        }

        .header-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: var(--success);
            font-family: 'JetBrains Mono', monospace;
            font-weight: 500;
        }

        @keyframes pulse {
            0%,100% { box-shadow: 0 0 0 0 rgba(22,163,74,0.5); }
            50%      { box-shadow: 0 0 0 5px rgba(22,163,74,0); }
        }

        /* ════════════════════════════
           MAIN
        ════════════════════════════ */
        main {
            flex: 1;
            padding: 56px 20px 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ── HERO ── */
        .page-hero {
            text-align: center;
            margin-bottom: 48px;
            animation: slideUp 0.5s cubic-bezier(0.16,1,0.3,1) both;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(22,163,74,0.08);
            border: 1.5px solid var(--border2);
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 12px;
            color: var(--accent);
            font-family: 'JetBrains Mono', monospace;
            font-weight: 500;
            margin-bottom: 16px;
        }

        .page-hero h1 {
            font-size: 36px;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: -0.8px;
            margin-bottom: 10px;
        }

        .page-hero h1 span { color: var(--accent); }

        .page-hero p {
            font-size: 14px;
            color: var(--muted);
            font-family: 'JetBrains Mono', monospace;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── POLICY CONTAINER ── */
        .policy-wrapper {
            width: 100%;
            max-width: 780px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            animation: slideUp 0.5s 0.1s cubic-bezier(0.16,1,0.3,1) both;
        }

        .policy-card {
            background: var(--panel);
            border: 1.5px solid var(--border2);
            border-radius: 18px;
            padding: 32px 36px;
            box-shadow: 0 4px 24px rgba(22,163,74,0.07);
            position: relative;
            overflow: hidden;
        }

        .policy-card::before {
            content: '';
            position: absolute;
            top: 0; left: 24px; right: 24px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent2), transparent);
            border-radius: 0 0 4px 4px;
        }

        .section-num {
            font-size: 10px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            color: var(--accent);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .policy-card h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            letter-spacing: -0.3px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-icon {
            width: 34px; height: 34px;
            border-radius: 8px;
            background: rgba(22,163,74,0.08);
            border: 1.5px solid var(--border2);
            display: grid;
            place-items: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .policy-card p {
            font-size: 14px;
            color: var(--light);
            line-height: 1.8;
            margin-bottom: 12px;
        }

        .policy-card p:last-child { margin-bottom: 0; }

        .policy-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 4px;
        }

        .policy-list li {
            font-size: 14px;
            color: var(--light);
            line-height: 1.7;
            padding-left: 20px;
            position: relative;
        }

        .policy-list li::before {
            content: '▸';
            position: absolute;
            left: 0;
            color: var(--accent2);
            font-size: 12px;
            top: 2px;
        }

        /* highlight box */
        .highlight-box {
            background: rgba(22,163,74,0.06);
            border: 1.5px solid var(--border2);
            border-radius: 10px;
            padding: 14px 18px;
            margin-top: 14px;
            font-size: 13px;
            color: var(--dark2);
            line-height: 1.7;
            font-weight: 500;
        }

        /* contact inline */
        .contact-inline {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .contact-pill {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 500;
            border: 1.5px solid var(--border2);
            background: rgba(22,163,74,0.05);
            color: var(--accent);
            text-decoration: none;
            transition: background 0.2s, transform 0.15s;
        }

        .contact-pill:hover {
            background: rgba(22,163,74,0.12);
            transform: translateY(-2px);
        }

        /* effective date banner */
        .effective-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, var(--dark), var(--dark2));
            border-radius: 14px;
            padding: 20px 28px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .effective-banner .eb-left strong {
            display: block;
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 3px;
        }

        .effective-banner .eb-left span {
            font-size: 12px;
            color: rgba(255,255,255,0.5);
            font-family: 'JetBrains Mono', monospace;
        }

        .effective-banner .eb-date {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            font-weight: 600;
            color: var(--accent3);
            border: 1.5px solid rgba(74,222,128,0.3);
            padding: 6px 16px;
            border-radius: 20px;
            background: rgba(74,222,128,0.08);
        }

        /* ════════════════════════════
           FOOTER
        ════════════════════════════ */
        footer {
            background: rgba(255,255,255,0.94);
            border-top: 1.5px solid var(--border2);
            backdrop-filter: blur(16px);
            padding: 16px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            box-shadow: 0 -2px 16px rgba(22,163,74,0.06);
        }

        .footer-left {
            font-size: 12px;
            color: var(--muted);
            font-family: 'JetBrains Mono', monospace;
        }
        .footer-left strong { color: var(--dark2); }

        .footer-center {
            display: flex;
            gap: 20px;
        }
        .footer-center a {
            font-size: 12px;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-center a:hover { color: var(--accent); }

        .footer-right {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: var(--success);
            font-family: 'JetBrains Mono', monospace;
            font-weight: 500;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 600px) {
            header { padding: 0 20px; }
            .header-tagline, .header-badge { display: none; }
            .policy-card { padding: 24px 20px; }
            .page-hero h1 { font-size: 26px; }
            footer { padding: 14px 20px; flex-direction: column; align-items: center; text-align: center; }
            .footer-center { display: none; }
        }
    </style>
</head>
<body>

<div class="page-wrapper">

    <!-- ══ HEADER ══ -->
    <header>
        <a class="header-brand" href="{{ route('login') }}">
            <div class="header-logo-box">FT</div>
            <span class="header-name">Fisc<span>Tech</span></span>
        </a>
        <div class="header-right">
            <span class="header-tagline">Fiscal Management System</span>
            <span class="header-badge">
                <span class="live-dot"></span> Live
            </span>
        </div>
    </header>

    <!-- ══ MAIN ══ -->
    <main>

        <!-- Hero -->
        <div class="page-hero">
            <div class="hero-badge">📄 Legal Document</div>
            <h1><span>Privacy</span> Policy</h1>
            <p>Last updated: {{ date('F d, Y') }}</p>
        </div>

        <div class="policy-wrapper">

            <!-- Effective Date -->
            <div class="effective-banner">
                <div class="eb-left">
                    <strong>FiscTech Privacy Policy</strong>
                    <span>This policy applies to all users of the FiscTech Fiscal Management System</span>
                </div>
                <div class="eb-date">Effective: {{ date('Y') }}</div>
            </div>

            <!-- 1. Introduction -->
            <div class="policy-card">
                <div class="section-num">Section 01</div>
                <h2><span class="section-icon">👋</span> Introduction</h2>
                <p>
                    Welcome to FiscTech, a Fiscal Management System developed and operated by Blessward Mutsotso.
                    We are committed to protecting your personal information and your right to privacy.
                </p>
                <p>
                    This Privacy Policy explains how we collect, use, store, and protect your information when
                    you use the FiscTech system. By accessing or using FiscTech, you agree to the terms
                    outlined in this policy.
                </p>
            </div>

            <!-- 2. Information We Collect -->
            <div class="policy-card">
                <div class="section-num">Section 02</div>
                <h2><span class="section-icon">📋</span> Information We Collect</h2>
                <p>We collect the following types of information when you use FiscTech:</p>
                <ul class="policy-list">
                    <li><strong>Account Information</strong> — your name, email address, and password when you register.</li>
                    <li><strong>Business Information</strong> — company name, tax identification numbers, and financial data you enter into the system.</li>
                    <li><strong>Transaction Data</strong> — invoices, receipts, tax records, and fiscal reports generated through the system.</li>
                    <li><strong>Usage Data</strong> — pages visited, actions performed, login timestamps, and IP addresses.</li>
                    <li><strong>Device Information</strong> — browser type, operating system, and device identifiers.</li>
                </ul>
            </div>

            <!-- 3. How We Use Your Information -->
            <div class="policy-card">
                <div class="section-num">Section 03</div>
                <h2><span class="section-icon">⚙️</span> How We Use Your Information</h2>
                <p>Your information is used solely to provide and improve the FiscTech service. Specifically:</p>
                <ul class="policy-list">
                    <li>To create and manage your user account securely.</li>
                    <li>To process and store fiscal and tax-related records on your behalf.</li>
                    <li>To generate reports, invoices, and compliance documents.</li>
                    <li>To send system notifications, updates, and support responses.</li>
                    <li>To detect, prevent, and address technical issues or security threats.</li>
                    <li>To comply with applicable legal and regulatory requirements.</li>
                </ul>
                <div class="highlight-box">
                    🔒 We do <strong>not</strong> sell, rent, or trade your personal or business data to any third parties under any circumstances.
                </div>
            </div>

            <!-- 4. Data Storage & Security -->
            <div class="policy-card">
                <div class="section-num">Section 04</div>
                <h2><span class="section-icon">🛡️</span> Data Storage &amp; Security</h2>
                <p>
                    All data entered into FiscTech is stored securely on protected servers. We implement
                    industry-standard security measures including:
                </p>
                <ul class="policy-list">
                    <li>Encrypted passwords using bcrypt hashing — passwords are never stored in plain text.</li>
                    <li>HTTPS encryption for all data transmitted between your browser and our servers.</li>
                    <li>CSRF protection on all forms to prevent cross-site request forgery attacks.</li>
                    <li>Regular database backups to prevent data loss.</li>
                    <li>Restricted server access limited to authorized administrators only.</li>
                </ul>
                <p>
                    While we take every reasonable precaution to protect your data, no system is 100%
                    immune to security risks. We encourage users to use strong, unique passwords and
                    to log out after each session.
                </p>
            </div>

            <!-- 5. Data Sharing -->
            <div class="policy-card">
                <div class="section-num">Section 05</div>
                <h2><span class="section-icon">🤝</span> Data Sharing</h2>
                <p>
                    FiscTech does not share your personal or business data with third parties except in the
                    following limited circumstances:
                </p>
                <ul class="policy-list">
                    <li><strong>Legal Compliance</strong> — if required by law, court order, or government regulation.</li>
                    <li><strong>Fraud Prevention</strong> — to detect or prevent fraudulent or illegal activity.</li>
                    <li><strong>Service Providers</strong> — trusted technical providers (e.g. hosting) who operate under strict confidentiality agreements and process data only as instructed by FiscTech.</li>
                </ul>
            </div>

            <!-- 6. Your Rights -->
            <div class="policy-card">
                <div class="section-num">Section 06</div>
                <h2><span class="section-icon">⚖️</span> Your Rights</h2>
                <p>As a FiscTech user, you have the following rights regarding your data:</p>
                <ul class="policy-list">
                    <li><strong>Access</strong> — you may request a copy of the personal data we hold about you.</li>
                    <li><strong>Correction</strong> — you may request correction of inaccurate or incomplete data.</li>
                    <li><strong>Deletion</strong> — you may request deletion of your account and associated data, subject to legal retention requirements.</li>
                    <li><strong>Objection</strong> — you may object to certain uses of your data.</li>
                    <li><strong>Portability</strong> — you may request your data in a portable format where technically feasible.</li>
                </ul>
                <p>To exercise any of these rights, contact us directly using the details below.</p>
            </div>

            <!-- 7. Cookies -->
            <div class="policy-card">
                <div class="section-num">Section 07</div>
                <h2><span class="section-icon">🍪</span> Cookies &amp; Sessions</h2>
                <p>
                    FiscTech uses cookies and session storage to keep you securely logged in and to maintain
                    system state across pages. These are essential for the system to function correctly.
                </p>
                <ul class="policy-list">
                    <li><strong>Session Cookies</strong> — temporary cookies that expire when you close your browser.</li>
                    <li><strong>Remember Me Cookies</strong> — longer-lived cookies set only when you opt in on the login page.</li>
                    <li><strong>CSRF Tokens</strong> — security tokens embedded in forms to protect against attacks.</li>
                </ul>
                <p>We do not use advertising cookies, analytics tracking cookies, or third-party tracking of any kind.</p>
            </div>

            <!-- 8. Data Retention -->
            <div class="policy-card">
                <div class="section-num">Section 08</div>
                <h2><span class="section-icon">📁</span> Data Retention</h2>
                <p>
                    We retain your data for as long as your account is active or as required by applicable
                    fiscal and tax laws in Zimbabwe. Financial and tax records may be required to be retained
                    for a minimum of 6 years in accordance with standard regulatory requirements.
                </p>
                <p>
                    Upon account deletion, personal identifiable information will be removed while anonymised
                    financial records may be retained for legal compliance purposes.
                </p>
            </div>

            <!-- 9. Changes to Policy -->
            <div class="policy-card">
                <div class="section-num">Section 09</div>
                <h2><span class="section-icon">🔄</span> Changes to This Policy</h2>
                <p>
                    We may update this Privacy Policy from time to time to reflect changes in the system,
                    legal requirements, or best practices. When we make significant changes, we will notify
                    users via the system dashboard or by email.
                </p>
                <p>
                    Continued use of FiscTech after any changes to this policy constitutes your acceptance
                    of the updated terms. We encourage you to review this policy periodically.
                </p>
            </div>

            <!-- 10. Contact -->
            <div class="policy-card">
                <div class="section-num">Section 10</div>
                <h2><span class="section-icon">📬</span> Contact Us</h2>
                <p>
                    If you have any questions, concerns, or requests regarding this Privacy Policy or how
                    your data is handled, please contact us directly:
                </p>
                <div class="contact-inline">
                    <a href="mailto:blesswardmutsotso404@gmail.com" class="contact-pill">
                        ✉️ blesswardmutsotso404@gmail.com
                    </a>
                    <a href="https://wa.me/263787780405" target="_blank" class="contact-pill">
                        💬 +263 787 780 405
                    </a>
                    <a href="https://wa.me/263713194219" target="_blank" class="contact-pill">
                        📱 +263 713 194 219
                    </a>
                </div>
            </div>

        </div>
    </main>

    <!-- ══ FOOTER ══ -->
    <footer>
        <div class="footer-left">
            &copy; <strong>FiscTech</strong> {{ date('Y') }}. All rights reserved.
        </div>
        <div class="footer-center">
            <a href="{{ route('privacy') }}" style="color: var(--accent); font-weight:600;">Privacy Policy</a>
            <a href="{{ route('terms') }}">Terms of Service</a>
            <a href="{{ route('support') }}">Support</a>
        </div>
        <div class="footer-right">
            <span class="live-dot"></span>
            All systems operational
        </div>
    </footer>

</div>

</body>
</html>