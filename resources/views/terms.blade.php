<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service</title>
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
            --warn:    #d97706;
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

        /* ── TOC ── */
        .toc-card {
            width: 100%;
            max-width: 780px;
            background: var(--panel);
            border: 1.5px solid var(--border2);
            border-radius: 18px;
            padding: 28px 36px;
            box-shadow: 0 4px 24px rgba(22,163,74,0.07);
            margin-bottom: 20px;
            animation: slideUp 0.5s 0.05s cubic-bezier(0.16,1,0.3,1) both;
        }

        .toc-title {
            font-size: 12px;
            font-family: 'JetBrains Mono', monospace;
            color: var(--accent);
            font-weight: 600;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .toc-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .toc-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: var(--light);
            text-decoration: none;
            padding: 7px 12px;
            border-radius: 8px;
            transition: background 0.2s, color 0.2s;
            border: 1px solid transparent;
        }

        .toc-item:hover {
            background: rgba(22,163,74,0.07);
            border-color: var(--border2);
            color: var(--accent);
        }

        .toc-num {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: var(--accent2);
            font-weight: 600;
            min-width: 24px;
        }

        /* ── POLICY WRAPPER ── */
        .policy-wrapper {
            width: 100%;
            max-width: 780px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            animation: slideUp 0.5s 0.1s cubic-bezier(0.16,1,0.3,1) both;
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

        /* policy card */
        .policy-card {
            background: var(--panel);
            border: 1.5px solid var(--border2);
            border-radius: 18px;
            padding: 32px 36px;
            box-shadow: 0 4px 24px rgba(22,163,74,0.07);
            position: relative;
            overflow: hidden;
            scroll-margin-top: 80px;
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
            margin-bottom: 12px;
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
            top: 3px;
        }

        /* highlight boxes */
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

        .warn-box {
            background: rgba(217,119,6,0.06);
            border: 1.5px solid rgba(217,119,6,0.25);
            border-radius: 10px;
            padding: 14px 18px;
            margin-top: 14px;
            font-size: 13px;
            color: #92400e;
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
            .toc-card { padding: 22px 20px; }
            .toc-grid { grid-template-columns: 1fr; }
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
            <div class="hero-badge">📜 Legal Document</div>
            <h1>Terms of <span>Service</span></h1>
            <p>Last updated: {{ date('F d, Y') }}</p>
        </div>

        <!-- Table of Contents -->
        <div class="toc-card">
            <div class="toc-title">📑 &nbsp;Table of Contents</div>
            <div class="toc-grid">
                <a href="#s1"  class="toc-item"><span class="toc-num">01</span> Acceptance of Terms</a>
                <a href="#s2"  class="toc-item"><span class="toc-num">02</span> Description of Service</a>
                <a href="#s3"  class="toc-item"><span class="toc-num">03</span> User Accounts</a>
                <a href="#s4"  class="toc-item"><span class="toc-num">04</span> Acceptable Use</a>
                <a href="#s5"  class="toc-item"><span class="toc-num">05</span> Prohibited Activities</a>
                <a href="#s6"  class="toc-item"><span class="toc-num">06</span> Data & Privacy</a>
                <a href="#s7"  class="toc-item"><span class="toc-num">07</span> Intellectual Property</a>
                <a href="#s8"  class="toc-item"><span class="toc-num">08</span> Availability & Uptime</a>
                <a href="#s9"  class="toc-item"><span class="toc-num">09</span> Limitation of Liability</a>
                <a href="#s10" class="toc-item"><span class="toc-num">10</span> Termination</a>
                <a href="#s11" class="toc-item"><span class="toc-num">11</span> Governing Law</a>
                <a href="#s12" class="toc-item"><span class="toc-num">12</span> Changes to Terms</a>
                <a href="#s13" class="toc-item"><span class="toc-num">13</span> Contact Information</a>
            </div>
        </div>

        <div class="policy-wrapper">

            <!-- Effective Banner -->
            <div class="effective-banner">
                <div class="eb-left">
                    <strong>FiscTech Terms of Service</strong>
                    <span>Please read these terms carefully before using the FiscTech system</span>
                </div>
                <div class="eb-date">Effective: {{ date('Y') }}</div>
            </div>

            <!-- S1: Acceptance -->
            <div class="policy-card" id="s1">
                <div class="section-num">Section 01</div>
                <h2><span class="section-icon">✅</span> Acceptance of Terms</h2>
                <p>
                    By accessing, registering for, or using the FiscTech Fiscal Management System
                    ("the System", "FiscTech", "the Service"), you agree to be bound by these
                    Terms of Service ("Terms"). If you do not agree to these Terms, you must not
                    access or use the system.
                </p>
                <p>
                    These Terms constitute a legally binding agreement between you ("the User") and
                    FiscTech, operated by Blessward Mutsotso. By clicking "Register" or logging in,
                    you confirm that you have read, understood, and accepted these Terms in full.
                </p>
                <div class="highlight-box">
                    📌 If you are using FiscTech on behalf of a business or organisation, you represent
                    that you have the authority to bind that entity to these Terms.
                </div>
            </div>

            <!-- S2: Description -->
            <div class="policy-card" id="s2">
                <div class="section-num">Section 02</div>
                <h2><span class="section-icon">🖥️</span> Description of Service</h2>
                <p>
                    FiscTech is a web-based Fiscal Management System designed to assist businesses and
                    individuals in managing tax records, invoices, financial reports, and fiscal compliance
                    obligations primarily in accordance with Zimbabwean financial regulations.
                </p>
                <p>The system provides the following core features:</p>
                <ul class="policy-list">
                    <li>User account management and role-based access control.</li>
                    <li>Tax record keeping including VAT, PAYE, and corporate tax tracking.</li>
                    <li>Invoice generation and management.</li>
                    <li>Financial reporting and fiscal compliance dashboards.</li>
                    <li>Secure data storage and retrieval of fiscal records.</li>
                </ul>
                <p>
                    FiscTech reserves the right to modify, suspend, or discontinue any feature of the
                    service at any time with reasonable notice to users.
                </p>
            </div>

            <!-- S3: User Accounts -->
            <div class="policy-card" id="s3">
                <div class="section-num">Section 03</div>
                <h2><span class="section-icon">👤</span> User Accounts</h2>
                <p>To access FiscTech you must create an account. By creating an account, you agree to:</p>
                <ul class="policy-list">
                    <li>Provide accurate, current, and complete information during registration.</li>
                    <li>Maintain and promptly update your account information to keep it accurate.</li>
                    <li>Keep your password confidential and not share it with any third party.</li>
                    <li>Notify FiscTech immediately of any unauthorised use of your account.</li>
                    <li>Accept full responsibility for all activities that occur under your account.</li>
                </ul>
                <div class="warn-box">
                    ⚠️ FiscTech will never ask for your password via email, WhatsApp, or any other
                    communication channel. If you receive such a request, treat it as fraudulent.
                </div>
            </div>

            <!-- S4: Acceptable Use -->
            <div class="policy-card" id="s4">
                <div class="section-num">Section 04</div>
                <h2><span class="section-icon">✔️</span> Acceptable Use</h2>
                <p>You agree to use FiscTech only for lawful purposes and in a manner consistent with all applicable laws and regulations, including Zimbabwean tax and fiscal laws. Acceptable uses include:</p>
                <ul class="policy-list">
                    <li>Managing your own or your organisation's legitimate tax and fiscal records.</li>
                    <li>Generating accurate invoices and financial reports for your business.</li>
                    <li>Accessing the system for purposes directly related to fiscal management and compliance.</li>
                    <li>Sharing access with authorised employees or accountants within your organisation.</li>
                </ul>
            </div>

            <!-- S5: Prohibited Activities -->
            <div class="policy-card" id="s5">
                <div class="section-num">Section 05</div>
                <h2><span class="section-icon">🚫</span> Prohibited Activities</h2>
                <p>The following activities are strictly prohibited when using FiscTech:</p>
                <ul class="policy-list">
                    <li>Entering false, fraudulent, or misleading fiscal or financial data into the system.</li>
                    <li>Using the system to facilitate tax evasion, money laundering, or any illegal financial activity.</li>
                    <li>Attempting to gain unauthorised access to other users' accounts or data.</li>
                    <li>Reverse engineering, decompiling, or attempting to extract the source code of the system.</li>
                    <li>Uploading malicious code, viruses, or any software intended to damage or disrupt the system.</li>
                    <li>Using automated bots, scrapers, or scripts to access the system without written permission.</li>
                    <li>Reselling, sublicensing, or redistributing access to FiscTech to third parties.</li>
                    <li>Impersonating another user, business, or FiscTech staff member.</li>
                </ul>
                <div class="warn-box">
                    ⚠️ Violation of these prohibitions may result in immediate account suspension,
                    permanent termination, and referral to relevant legal authorities where applicable.
                </div>
            </div>

            <!-- S6: Data & Privacy -->
            <div class="policy-card" id="s6">
                <div class="section-num">Section 06</div>
                <h2><span class="section-icon">🔒</span> Data &amp; Privacy</h2>
                <p>
                    Your use of FiscTech is also governed by our
                    <a href="{{ route('privacy') }}" style="color:var(--accent); font-weight:600;">Privacy Policy</a>,
                    which is incorporated into these Terms by reference. By using the system, you
                    consent to the collection and use of your data as described therein.
                </p>
                <p>You acknowledge and agree that:</p>
                <ul class="policy-list">
                    <li>You are responsible for the accuracy of the data you enter into the system.</li>
                    <li>FiscTech is not responsible for errors arising from incorrect data entry by users.</li>
                    <li>You are responsible for maintaining backups of critical financial data where required.</li>
                    <li>FiscTech will implement reasonable security measures but cannot guarantee absolute data security.</li>
                </ul>
            </div>

            <!-- S7: Intellectual Property -->
            <div class="policy-card" id="s7">
                <div class="section-num">Section 07</div>
                <h2><span class="section-icon">💡</span> Intellectual Property</h2>
                <p>
                    FiscTech, including its design, codebase, features, branding, logos, and all
                    associated intellectual property, is owned exclusively by Blessward Mutsotso.
                    All rights are reserved.
                </p>
                <p>
                    Users are granted a limited, non-exclusive, non-transferable licence to access
                    and use the system solely for its intended purpose as described in these Terms.
                    This licence does not include the right to:
                </p>
                <ul class="policy-list">
                    <li>Copy, reproduce, or distribute any part of the FiscTech system.</li>
                    <li>Modify or create derivative works based on the system.</li>
                    <li>Use FiscTech's branding, logos, or name for commercial purposes without written permission.</li>
                </ul>
                <div class="highlight-box">
                    ✅ Your own data and records entered into FiscTech remain your property at all times.
                    FiscTech claims no ownership over your business or financial data.
                </div>
            </div>

            <!-- S8: Availability -->
            <div class="policy-card" id="s8">
                <div class="section-num">Section 08</div>
                <h2><span class="section-icon">🟢</span> Availability &amp; Uptime</h2>
                <p>
                    FiscTech aims to provide reliable, uninterrupted access to the system. However,
                    we do not guarantee 100% uptime. The system may be temporarily unavailable due to:
                </p>
                <ul class="policy-list">
                    <li>Scheduled maintenance windows (announced in advance where possible).</li>
                    <li>Unplanned technical issues or server outages.</li>
                    <li>Third-party infrastructure failures (hosting provider, internet connectivity).</li>
                    <li>Force majeure events outside our reasonable control.</li>
                </ul>
                <p>
                    We will make reasonable efforts to restore service as quickly as possible in the
                    event of any outage and will communicate status updates via support channels.
                </p>
            </div>

            <!-- S9: Limitation of Liability -->
            <div class="policy-card" id="s9">
                <div class="section-num">Section 09</div>
                <h2><span class="section-icon">⚖️</span> Limitation of Liability</h2>
                <p>
                    To the fullest extent permitted by applicable law, FiscTech and its developer
                    shall not be liable for any indirect, incidental, special, consequential, or
                    punitive damages arising from your use of or inability to use the system.
                </p>
                <p>FiscTech specifically disclaims liability for:</p>
                <ul class="policy-list">
                    <li>Financial losses resulting from incorrect data entry by the user.</li>
                    <li>Tax penalties or regulatory fines resulting from user error or misuse of the system.</li>
                    <li>Loss of data due to user error, hardware failure, or events beyond our control.</li>
                    <li>Decisions made by users based on reports or data generated by the system.</li>
                    <li>Downtime, service interruptions, or data breaches caused by third-party providers.</li>
                </ul>
                <div class="warn-box">
                    ⚠️ FiscTech is a tool to assist with fiscal management. It does not replace the
                    advice of a qualified accountant, tax professional, or legal adviser.
                </div>
            </div>

            <!-- S10: Termination -->
            <div class="policy-card" id="s10">
                <div class="section-num">Section 10</div>
                <h2><span class="section-icon">🔴</span> Termination</h2>
                <p>
                    Either party may terminate access to FiscTech at any time.
                </p>
                <p><strong>You may terminate</strong> your account by contacting support and requesting deletion. Upon termination, your access will be revoked and your personal data handled in accordance with our Privacy Policy.</p>
                <p><strong>FiscTech may suspend or terminate</strong> your account immediately and without notice if:</p>
                <ul class="policy-list">
                    <li>You breach any provision of these Terms.</li>
                    <li>You engage in fraudulent, illegal, or harmful activities through the system.</li>
                    <li>Your account is involved in a security incident or unauthorised access.</li>
                    <li>You attempt to disrupt or damage the system or other users' data.</li>
                </ul>
                <p>
                    Upon termination, your right to access the system ceases immediately. Provisions
                    of these Terms that by their nature should survive termination will do so.
                </p>
            </div>

            <!-- S11: Governing Law -->
            <div class="policy-card" id="s11">
                <div class="section-num">Section 11</div>
                <h2><span class="section-icon">🏛️</span> Governing Law</h2>
                <p>
                    These Terms of Service shall be governed by and construed in accordance with
                    the laws of the Republic of Zimbabwe. Any disputes arising from or in connection
                    with these Terms shall be subject to the exclusive jurisdiction of the courts
                    of Zimbabwe.
                </p>
                <p>
                    Users agree to first attempt to resolve any dispute informally by contacting
                    FiscTech support before initiating any formal legal proceedings.
                </p>
            </div>

            <!-- S12: Changes -->
            <div class="policy-card" id="s12">
                <div class="section-num">Section 12</div>
                <h2><span class="section-icon">🔄</span> Changes to These Terms</h2>
                <p>
                    FiscTech reserves the right to modify these Terms of Service at any time.
                    When changes are made, the "Last updated" date at the top of this page will
                    be revised accordingly.
                </p>
                <p>
                    For significant changes, we will notify users via the system dashboard, email,
                    or WhatsApp. Your continued use of FiscTech after any changes constitutes your
                    acceptance of the revised Terms. If you disagree with the updated Terms, you
                    must stop using the system and contact support to close your account.
                </p>
            </div>

            <!-- S13: Contact -->
            <div class="policy-card" id="s13">
                <div class="section-num">Section 13</div>
                <h2><span class="section-icon">📬</span> Contact Information</h2>
                <p>
                    For any questions, concerns, or clarifications regarding these Terms of Service,
                    please contact us directly. We aim to respond to all enquiries within 24–48 hours.
                </p>
                <p><strong>Blessward Mutsotso</strong> — Developer &amp; System Administrator, FiscTech</p>
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
            <a href="{{ route('privacy') }}">Privacy Policy</a>
            <a href="{{ route('terms') }}" style="color: var(--accent); font-weight:600;">Terms of Service</a>
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