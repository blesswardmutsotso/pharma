<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support</title>
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
            height: 100%;
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
            padding: 56px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ── PAGE HERO ── */
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
            font-size: 15px;
            color: var(--muted);
            max-width: 480px;
            margin: 0 auto;
            line-height: 1.6;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ════════════════════════════
           SUPPORT GRID
        ════════════════════════════ */
        .support-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            width: 100%;
            max-width: 860px;
            margin-bottom: 28px;
        }

        .support-card {
            background: var(--panel);
            border: 1.5px solid var(--border2);
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 4px 24px rgba(22,163,74,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            animation: slideUp 0.5s cubic-bezier(0.16,1,0.3,1) both;
            position: relative;
            overflow: hidden;
        }

        .support-card::before {
            content: '';
            position: absolute;
            top: 0; left: 20px; right: 20px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent2), transparent);
            border-radius: 0 0 4px 4px;
        }

        .support-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 36px rgba(22,163,74,0.14);
        }

        .support-card:nth-child(1) { animation-delay: 0.05s; }
        .support-card:nth-child(2) { animation-delay: 0.1s; }
        .support-card:nth-child(3) { animation-delay: 0.15s; }
        .support-card:nth-child(4) { animation-delay: 0.2s; }

        .card-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 22px;
            margin-bottom: 14px;
            background: rgba(22,163,74,0.08);
            border: 1.5px solid var(--border2);
        }

        .card-type {
            font-size: 10px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            color: var(--accent);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
            letter-spacing: -0.2px;
        }

        .card-value {
            font-size: 14px;
            color: var(--light);
            font-family: 'JetBrains Mono', monospace;
            margin-bottom: 16px;
            word-break: break-all;
        }

        .card-value a {
            color: var(--accent);
            text-decoration: none;
            transition: color 0.2s;
        }
        .card-value a:hover { color: var(--dark2); text-decoration: underline; }

        .card-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .tag {
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            padding: 3px 10px;
            border-radius: 20px;
            border: 1px solid var(--border2);
            color: var(--accent);
            background: rgba(22,163,74,0.06);
        }

        /* ── PROFILE CARD (full width) ── */
        .profile-card {
            background: linear-gradient(135deg, var(--dark), var(--dark2));
            border: 1.5px solid rgba(74,222,128,0.2);
            border-radius: 18px;
            padding: 32px 36px;
            width: 100%;
            max-width: 860px;
            display: flex;
            align-items: center;
            gap: 28px;
            box-shadow: 0 8px 40px rgba(22,163,74,0.18);
            animation: slideUp 0.5s 0.25s cubic-bezier(0.16,1,0.3,1) both;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
        }

        .profile-card::after {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 180px; height: 180px;
            background: radial-gradient(circle, rgba(74,222,128,0.12), transparent 70%);
            border-radius: 50%;
        }

        .profile-avatar {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent2), var(--accent));
            display: grid;
            place-items: center;
            font-size: 28px;
            font-weight: 800;
            color: #fff;
            flex-shrink: 0;
            border: 3px solid rgba(74,222,128,0.4);
            font-family: 'Sora', sans-serif;
            box-shadow: 0 4px 20px rgba(34,197,94,0.3);
        }

        .profile-info { flex: 1; }

        .profile-role {
            font-size: 10px;
            font-family: 'JetBrains Mono', monospace;
            color: var(--accent3);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .profile-name {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.4px;
            margin-bottom: 6px;
        }

        .profile-desc {
            font-size: 13px;
            color: rgba(255,255,255,0.5);
            line-height: 1.5;
        }

        .profile-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex-shrink: 0;
        }

        .btn-wa {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #25d366;
            color: #fff;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 14px rgba(37,211,102,0.4);
            font-family: 'Sora', sans-serif;
        }

        .btn-wa:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37,211,102,0.5);
        }

        .btn-call {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: rgba(255,255,255,0.1);
            color: #fff;
            border: 1.5px solid rgba(255,255,255,0.2);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s, transform 0.15s;
            font-family: 'Sora', sans-serif;
        }

        .btn-call:hover {
            background: rgba(255,255,255,0.18);
            transform: translateY(-2px);
        }

        /* ── AVAILABILITY BANNER ── */
        .avail-banner {
            width: 100%;
            max-width: 860px;
            background: var(--panel);
            border: 1.5px solid var(--border2);
            border-radius: 14px;
            padding: 18px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            animation: slideUp 0.5s 0.3s cubic-bezier(0.16,1,0.3,1) both;
            box-shadow: 0 4px 20px rgba(22,163,74,0.07);
        }

        .avail-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avail-icon {
            font-size: 22px;
        }

        .avail-text strong {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 2px;
        }

        .avail-text span {
            font-size: 12px;
            color: var(--muted);
        }

        .avail-channels {
            display: flex;
            gap: 8px;
        }

        .channel-tag {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            font-family: 'JetBrains Mono', monospace;
        }

        .channel-tag.wa  { background: rgba(37,211,102,0.1); color: #16a34a; border: 1px solid rgba(37,211,102,0.3); }
        .channel-tag.ph  { background: rgba(22,163,74,0.08); color: var(--accent); border: 1px solid var(--border2); }
        .channel-tag.em  { background: rgba(59,130,246,0.08); color: #2563eb; border: 1px solid rgba(59,130,246,0.2); }

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
        @media (max-width: 700px) {
            .support-grid { grid-template-columns: 1fr; }
            .profile-card { flex-direction: column; text-align: center; }
            .profile-actions { flex-direction: row; justify-content: center; }
            .avail-channels { flex-wrap: wrap; justify-content: center; }
            header { padding: 0 20px; }
            .header-tagline, .header-badge { display: none; }
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
            <div class="hero-badge">
                <span class="live-dot"></span>
                Support Available Now
            </div>
            <h1>We're here to <span>help you</span></h1>
            <p>Reach out to our support team directly — available via WhatsApp, phone call, or email for any technical or system issues.</p>
        </div>

        <!-- Contact Cards Grid -->
        <div class="support-grid">

            <!-- WhatsApp 1 -->
            <div class="support-card">
                <div class="card-icon">💬</div>
                <div class="card-type">WhatsApp · Primary</div>
                <div class="card-title">Chat on WhatsApp</div>
                <div class="card-value">
                    <a href="https://wa.me/263787780405" target="_blank">+263 787 780 405</a>
                </div>
                <div class="card-tags">
                    <span class="tag">WhatsApp</span>
                    <span class="tag">Calls</span>
                    <span class="tag">Primary</span>
                </div>
            </div>

            <!-- WhatsApp 2 -->
            <div class="support-card">
                <div class="card-icon">📱</div>
                <div class="card-type">WhatsApp · Secondary</div>
                <div class="card-title">Alternate Number</div>
                <div class="card-value">
                    <a href="https://wa.me/263713194219" target="_blank">+263 713 194 219</a>
                </div>
                <div class="card-tags">
                    <span class="tag">WhatsApp</span>
                    <span class="tag">Calls</span>
                    <span class="tag">Backup</span>
                </div>
            </div>

            <!-- Email -->
            <div class="support-card">
                <div class="card-icon">✉️</div>
                <div class="card-type">Email Support</div>
                <div class="card-title">Send an Email</div>
                <div class="card-value">
                    <a href="mailto:blesswardmutsotso404@gmail.com">blesswardmutsotso404@gmail.com</a>
                </div>
                <div class="card-tags">
                    <span class="tag">Email</span>
                    <span class="tag">24–48hr Response</span>
                </div>
            </div>

            <!-- Hours -->
            <div class="support-card">
                <div class="card-icon">🕐</div>
                <div class="card-type">Availability</div>
                <div class="card-title">Support Hours</div>
                <div class="card-value">Mon – Sat &nbsp;·&nbsp; 8:00 AM – 8:00 PM</div>
                <div class="card-tags">
                    <span class="tag">CAT Timezone</span>
                    <span class="tag">Zimbabwe</span>
                </div>
            </div>

        </div>

        <!-- Developer Profile Card -->
        <div class="profile-card">
            <div class="profile-avatar">BM</div>
            <div class="profile-info">
                <div class="profile-role">System Developer &amp; Support Lead</div>
                <div class="profile-name">Blessward Mutsotso</div>
                <div class="profile-desc">
                    FiscTech developer and primary point of contact for system support,
                    troubleshooting, and technical queries. Available on WhatsApp and calls.
                </div>
            </div>
            <div class="profile-actions">
                <a href="https://wa.me/263787780405" target="_blank" class="btn-wa">
                    💬 &nbsp;WhatsApp
                </a>
                <a href="tel:+263787780405" class="btn-call">
                    📞 &nbsp;Call Now
                </a>
            </div>
        </div>

        <!-- Availability Banner -->
        <div class="avail-banner">
            <div class="avail-left">
                <div class="avail-icon">🟢</div>
                <div class="avail-text">
                    <strong>Available on multiple channels</strong>
                    <span>Reach Blessward directly via any of the options below</span>
                </div>
            </div>
            <div class="avail-channels">
                <span class="channel-tag wa">💬 WhatsApp</span>
                <span class="channel-tag ph">📞 Phone Call</span>
                <span class="channel-tag em">✉️ Email</span>
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
            <a href="{{ route('terms') }}">Terms of Service</a>
            <a href="{{ route('support') }}" style="color: var(--accent); font-weight:600;">Support</a>
        </div>
        <div class="footer-right">
            <span class="live-dot"></span>
            All systems operational
        </div>
    </footer>

</div>

</body>
</html>