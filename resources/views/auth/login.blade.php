<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:      #eff6ff;
            --bg2:     #ffffff;
            --panel:   #ffffff;
            --border:  #dbeafe;
            --border2: #93c5fd;
            --accent:  #3b82f6;
            --accent2: #60a5fa;
            --accent3: #93c5fd;
            --dark:    #172554;
            --dark2:   #1e3a8a;
            --text:    #1e3a5f;
            --muted:   #6b7280;
            --light:   #374151;
            --error:   #dc2626;
            --success: #3b82f6;
            --white:   #ffffff;
        }

        html, body {
            height: 100%;
            font-family: 'Sora', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            overflow-x: hidden;
        }

        /* ── FLOATING SYMBOLS ── */
        .finance-canvas { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
        .symbol { position: absolute; font-family: 'JetBrains Mono', monospace; font-weight: 500; opacity: 0; animation: floatUp linear infinite; color: var(--accent); user-select: none; }
        @keyframes floatUp {
            0%   { opacity: 0;    transform: translateY(0) rotate(0deg); }
            10%  { opacity: 0.18; }
            80%  { opacity: 0.12; }
            100% { opacity: 0;    transform: translateY(-100vh) rotate(20deg); }
        }

        /* ── TICKER ── */
        .ticker-wrap { position: fixed; bottom: 64px; left: 0; right: 0; overflow: hidden; pointer-events: none; z-index: 0; opacity: 0.28; border-top: 1px solid var(--border2); border-bottom: 1px solid var(--border2); background: rgba(255,255,255,0.6); padding: 4px 0; }
        .ticker-tape { display: flex; animation: ticker 30s linear infinite; white-space: nowrap; }
        .ticker-tape span { display: inline-block; padding: 0 28px; font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.5px; }
        .ticker-tape span.up   { color: #16a34a; }
        .ticker-tape span.down { color: #dc2626; }
        .ticker-tape span.sep  { color: #9ca3af; }
        @keyframes ticker { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }

        /* ── DOT GRID ── */
        body::before { content: ''; position: fixed; inset: 0; background-image: radial-gradient(circle, rgba(59,130,246,0.1) 1px, transparent 1px); background-size: 28px 28px; pointer-events: none; z-index: 0; }

        /* ── LAYOUT ── */
        .page-wrapper { display: flex; flex-direction: column; min-height: 100vh; position: relative; z-index: 1; }

        /* ── HEADER ── */
        header { display: flex; align-items: center; justify-content: space-between; padding: 0 40px; height: 64px; background: rgba(255,255,255,0.94); border-bottom: 1.5px solid var(--border2); backdrop-filter: blur(16px); position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 16px rgba(59,130,246,0.08); }
        .header-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .header-logo-box { width: 52px; height: 52px; background: linear-gradient(135deg, var(--accent), #2563eb); border-radius: 12px; display: grid; place-items: center; font-family: 'JetBrains Mono', monospace; font-weight: 600; font-size: 13px; color: #fff; flex-shrink: 0; box-shadow: 0 3px 10px rgba(59,130,246,0.35); }
        .header-name { font-size: 19px; font-weight: 800; color: var(--dark); letter-spacing: -0.5px; }
        .header-name span { color: var(--accent); }
        .header-right { display: flex; align-items: center; gap: 12px; }
        .header-tagline { font-size: 11px; font-weight: 500; color: var(--muted); font-family: 'JetBrains Mono', monospace; letter-spacing: 0.4px; border: 1.5px solid var(--border2); padding: 3px 12px; border-radius: 20px; background: rgba(59,130,246,0.05); }
        .live-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--success); display: inline-block; animation: pulse 2s infinite; }
        .header-badge { display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--success); font-family: 'JetBrains Mono', monospace; font-weight: 500; }
        @keyframes pulse { 0%,100% { box-shadow: 0 0 0 0 rgba(59,130,246,0.5); } 50% { box-shadow: 0 0 0 5px rgba(59,130,246,0); } }

        /* ── HEADER DEMO BUTTON ── */
        .btn-demo-header {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px;
            background: linear-gradient(135deg, var(--accent), #2563eb);
            color: #fff; border: none; border-radius: 9px;
            font-size: 12.5px; font-weight: 700; font-family: 'Sora', sans-serif;
            cursor: pointer; white-space: nowrap; letter-spacing: 0.1px;
            box-shadow: 0 3px 12px rgba(59,130,246,0.38);
            transition: transform 0.15s, box-shadow 0.2s, filter 0.2s;
        }
        .btn-demo-header:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(59,130,246,0.44); filter: brightness(1.07); }

        /* ── MAIN ── */
        main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 48px 20px; }
        .login-container { width: 100%; max-width: 440px; position: relative; }

        .stat-chip { position: absolute; background: var(--white); border: 1.5px solid var(--border2); border-radius: 12px; padding: 10px 16px; box-shadow: 0 4px 20px rgba(59,130,246,0.12); animation: chipFloat 4s ease-in-out infinite; pointer-events: none; z-index: 2; min-width: 120px; }
        .chip-label { color: var(--muted); font-size: 10px; font-family: 'JetBrains Mono', monospace; }
        .chip-val   { color: var(--accent); font-weight: 700; font-size: 14px; margin-top: 3px; font-family: 'JetBrains Mono', monospace; }
        .chip-delta { font-size: 10px; color: var(--success); font-family: 'JetBrains Mono', monospace; margin-top: 1px; }
        .stat-chip.left  { left: -140px; top: 28%; }
        .stat-chip.right { right: -140px; top: 56%; animation-delay: 1.8s; }
        @keyframes chipFloat { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-9px); } }

        .card-glow { position: absolute; width: 440px; height: 440px; background: radial-gradient(circle, rgba(96,165,250,0.1) 0%, transparent 70%); border-radius: 50%; top: 50%; left: 50%; transform: translate(-50%,-50%); pointer-events: none; filter: blur(24px); }

        .login-card { position: relative; background: var(--panel); border: 1.5px solid var(--border2); border-radius: 20px; padding: 40px 40px 36px; box-shadow: 0 0 0 1px rgba(59,130,246,0.05), 0 24px 64px rgba(59,130,246,0.1), 0 4px 16px rgba(0,0,0,0.05); animation: slideUp 0.55s cubic-bezier(0.16,1,0.3,1) both; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(28px); } to { opacity: 1; transform: translateY(0); } }
        .login-card::before { content: ''; position: absolute; top: 0; left: 28px; right: 28px; height: 3px; background: linear-gradient(90deg, transparent, var(--accent2), var(--accent3), var(--accent2), transparent); border-radius: 0 0 6px 6px; }

        /* card header */
        .card-header { text-align: center; margin-bottom: 24px; }
        .card-logo-wrap { position: relative; display: inline-block; margin-bottom: 16px; }
        .card-logo { width: 110px; height: 110px; background: linear-gradient(135deg, var(--accent), #2563eb); border-radius: 24px; display: inline-grid; place-items: center; font-family: 'JetBrains Mono', monospace; font-size: 22px; font-weight: 700; color: #fff; box-shadow: 0 12px 40px rgba(59,130,246,0.4); position: relative; z-index: 1; }
        .card-logo-wrap::before { content: ''; position: absolute; inset: -8px; border-radius: 32px; border: 2px dashed rgba(96,165,250,0.4); animation: spin 14s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .card-title { font-size: 23px; font-weight: 800; color: var(--dark); letter-spacing: -0.5px; margin-bottom: 5px; }
        .card-subtitle { font-size: 13px; color: var(--muted); }

        /* ── DEMO BANNER (inside card) ── */
        .demo-banner { display: flex; align-items: center; justify-content: space-between; gap: 12px; background: linear-gradient(135deg, rgba(59,130,246,0.05), rgba(74,222,128,0.07)); border: 1.5px solid var(--border2); border-radius: 12px; padding: 12px 16px; margin-bottom: 24px; }
        .demo-banner-title { font-size: 12.5px; font-weight: 700; color: var(--dark2); }
        .demo-banner-sub   { font-size: 11px; color: var(--muted); font-family: 'JetBrains Mono', monospace; margin-top: 2px; }
        .btn-demo-card { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: #fff; color: var(--accent); border: 1.5px solid var(--accent2); border-radius: 9px; font-size: 12.5px; font-weight: 700; font-family: 'Sora', sans-serif; cursor: pointer; white-space: nowrap; flex-shrink: 0; transition: background 0.2s, color 0.2s, box-shadow 0.2s, transform 0.15s; }
        .btn-demo-card:hover { background: var(--accent); color: #fff; box-shadow: 0 4px 14px rgba(59,130,246,0.28); transform: translateY(-1px); }

        /* ── STATUS ── */
        .status-alert { background: rgba(59,130,246,0.07); border: 1.5px solid rgba(59,130,246,0.25); border-radius: 10px; padding: 10px 14px; font-size: 13px; color: var(--success); margin-bottom: 20px; }

        /* ── FORM ── */
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 12.5px; font-weight: 600; color: var(--light); margin-bottom: 6px; letter-spacing: 0.2px; }
        .input-wrap { position: relative; }
        .input-icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); font-size: 15px; pointer-events: none; }
        input[type="email"], input[type="password"] { width: 100%; background: #f0f7ff; border: 1.5px solid #d1fae5; border-radius: 10px; padding: 11px 14px 11px 40px; font-size: 14px; font-family: 'Sora', sans-serif; color: var(--text); outline: none; transition: border-color 0.2s, box-shadow 0.2s, background 0.2s; }
        input:focus { border-color: var(--accent2); background: #fff; box-shadow: 0 0 0 3px rgba(96,165,250,0.12); }
        input::placeholder { color: #9ca3af; }
        .input-error { margin-top: 5px; font-size: 12px; color: var(--error); }

        .btn-login { width: 100%; padding: 12px; background: linear-gradient(135deg, var(--accent), #2563eb); color: #fff; border: none; border-radius: 10px; font-size: 15px; font-weight: 700; font-family: 'Sora', sans-serif; cursor: pointer; transition: transform 0.15s, box-shadow 0.2s, filter 0.2s; box-shadow: 0 4px 18px rgba(59,130,246,0.38); position: relative; overflow: hidden; }
        .btn-login::after { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent); opacity: 0; transition: opacity 0.2s; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 26px rgba(59,130,246,0.45); filter: brightness(1.06); }
        .btn-login:hover::after { opacity: 1; }
        .btn-login:active { transform: translateY(0); }

        .divider { display: flex; align-items: center; gap: 12px; margin: 22px 0; color: #9ca3af; font-size: 12px; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        /* ── FOOTER ── */
        footer { background: rgba(255,255,255,0.94); border-top: 1.5px solid var(--border2); backdrop-filter: blur(16px); padding: 16px 40px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; box-shadow: 0 -2px 16px rgba(59,130,246,0.06); }
        .footer-left { font-size: 12px; color: var(--muted); font-family: 'JetBrains Mono', monospace; }
        .footer-left strong { color: var(--dark2); }
        .footer-center { display: flex; gap: 20px; }
        .footer-center a { font-size: 12px; color: var(--muted); text-decoration: none; transition: color 0.2s; }
        .footer-center a:hover { color: var(--accent); }
        .footer-right { display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--success); font-family: 'JetBrains Mono', monospace; font-weight: 500; }

        /* ══════════════════════════════════════
           BOOK A DEMO MODAL
        ══════════════════════════════════════ */
        .demo-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(23,37,84,0.58);
            backdrop-filter: blur(8px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .demo-overlay.open { display: flex; }

        .demo-modal {
            position: relative;
            background: #fff;
            border: 1.5px solid var(--border2);
            border-radius: 22px;
            width: 100%; max-width: 660px;
            max-height: 92vh;
            overflow-y: auto;
            padding: 42px 40px 38px;
            box-shadow: 0 32px 80px rgba(59,130,246,0.2), 0 8px 24px rgba(0,0,0,0.1);
            animation: modalPop 0.38s cubic-bezier(0.16,1,0.3,1) both;
            scrollbar-width: thin;
            scrollbar-color: var(--border2) transparent;
        }
        .demo-modal::-webkit-scrollbar { width: 5px; }
        .demo-modal::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 99px; }

        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.93) translateY(22px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        .demo-modal::before { content: ''; position: absolute; top: 0; left: 28px; right: 28px; height: 3px; background: linear-gradient(90deg, transparent, var(--accent2), var(--accent3), var(--accent2), transparent); border-radius: 0 0 6px 6px; }

        .demo-close { position: absolute; top: 14px; right: 14px; width: 32px; height: 32px; border: 1.5px solid var(--border); background: #f9fafb; border-radius: 8px; font-size: 14px; color: var(--muted); cursor: pointer; display: grid; place-items: center; transition: background 0.18s, color 0.18s, border-color 0.18s; z-index: 10; }
        .demo-close:hover { background: var(--error); color: #fff; border-color: var(--error); }

        .demo-hd { text-align: center; margin-bottom: 28px; }
        .demo-hd-logo-wrap { position: relative; display: inline-block; margin-bottom: 14px; }
        .demo-hd-logo { width: 86px; height: 86px; background: linear-gradient(135deg, var(--accent), #2563eb); border-radius: 22px; display: inline-grid; place-items: center; font-family: 'JetBrains Mono', monospace; font-size: 20px; font-weight: 700; color: #fff; box-shadow: 0 8px 28px rgba(59,130,246,0.38); position: relative; z-index: 1; }
        .demo-hd-logo-wrap::before { content: ''; position: absolute; inset: -7px; border-radius: 28px; border: 2px dashed rgba(96,165,250,0.4); animation: spin 14s linear infinite; }
        .demo-hd-title { font-size: 22px; font-weight: 800; color: var(--dark); letter-spacing: -0.4px; margin-bottom: 5px; }
        .demo-hd-sub   { font-size: 13px; color: var(--muted); margin-bottom: 14px; }
        .demo-badges   { display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap; }
        .demo-badge    { background: rgba(59,130,246,0.07); border: 1.5px solid var(--border2); border-radius: 20px; padding: 3px 12px; font-size: 11px; font-weight: 600; color: var(--accent); font-family: 'JetBrains Mono', monospace; }

        .demo-sec { font-size: 10px; font-weight: 700; font-family: 'JetBrains Mono', monospace; color: var(--muted); letter-spacing: 1.5px; text-transform: uppercase; border-bottom: 1px solid var(--border); padding-bottom: 7px; margin: 22px 0 14px; }

        .d-grid  { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .d-grid.full { grid-template-columns: 1fr; }

        .d-field { display: flex; flex-direction: column; gap: 5px; margin-bottom: 4px; }
        .d-field > label { font-size: 12px; font-weight: 600; color: var(--light); margin: 0; }
        .d-field .req    { color: var(--error); }
        .d-wrap          { position: relative; }
        .d-ico           { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 14px; pointer-events: none; line-height: 1; }

        .d-field input {
            width: 100%; background: #f0f7ff; border: 1.5px solid var(--border);
            border-radius: 9px; padding: 10px 12px 10px 38px;
            font-size: 13.5px; font-family: 'Sora', sans-serif; color: var(--text);
            outline: none; transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .d-field input:focus   { border-color: var(--accent2); background: #fff; box-shadow: 0 0 0 3px rgba(96,165,250,0.11); }
        .d-field input.d-err   { border-color: var(--error); background: #fff8f8; box-shadow: 0 0 0 3px rgba(220,38,38,0.08); }
        .d-field input.d-ok    { border-color: var(--accent2); background: #eff6ff; }
        .d-field input::placeholder { color: #b5bfc9; }

        .d-errmsg { font-size: 11.5px; color: var(--error); min-height: 16px; }
        .d-hint   { font-size: 11px; color: var(--muted); font-family: 'JetBrains Mono', monospace; }

        /* ── CALENDAR STATUS BANNER ── */
        .cal-status {
            display: none;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 12.5px;
            font-weight: 600;
            margin-top: 14px;
            animation: slideUp 0.2s ease;
        }
        .cal-status.show { display: flex; }
        .cal-status.cal-ok      { background: rgba(59,130,246,0.08); border: 1.5px solid var(--border2); color: var(--dark2); }
        .cal-status.cal-fail    { background: #fff8f8; border: 1.5px solid #fecaca; color: #991b1b; }
        .cal-status.cal-loading { background: rgba(59,130,246,0.05); border: 1.5px solid var(--border); color: var(--muted); }

        /* submit */
        .demo-submit {
            width: 100%; margin-top: 24px; padding: 13px;
            background: linear-gradient(135deg, var(--accent), #2563eb);
            color: #fff; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 700; font-family: 'Sora', sans-serif;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 4px 18px rgba(59,130,246,0.38);
            transition: transform 0.15s, box-shadow 0.2s, filter 0.2s;
        }
        .demo-submit:hover   { transform: translateY(-2px); box-shadow: 0 8px 26px rgba(59,130,246,0.44); filter: brightness(1.06); }
        .demo-submit:active  { transform: translateY(0); }
        .demo-submit:disabled{ opacity: 0.6; cursor: not-allowed; transform: none; }

        .d-spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.35); border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite; display: none; }

        /* success */
        .demo-success { display: none; text-align: center; padding: 20px 0 8px; animation: slideUp 0.4s cubic-bezier(0.16,1,0.3,1) both; }
        .demo-success-icon  { font-size: 56px; margin-bottom: 16px; }
        .demo-success-title { font-size: 22px; font-weight: 800; color: var(--dark); margin-bottom: 8px; }
        .demo-success-msg   { font-size: 13.5px; color: var(--muted); line-height: 1.7; margin-bottom: 26px; }
        .demo-success-detail { display: inline-flex; align-items: center; gap: 8px; background: rgba(59,130,246,0.07); border: 1.5px solid var(--border2); border-radius: 10px; padding: 10px 18px; font-size: 12px; color: var(--dark2); font-family: 'JetBrains Mono', monospace; margin-bottom: 24px; }
        .demo-success-btn   { padding: 11px 38px; background: linear-gradient(135deg, var(--accent), #2563eb); color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 700; font-family: 'Sora', sans-serif; cursor: pointer; box-shadow: 0 4px 14px rgba(59,130,246,0.3); transition: filter 0.2s, transform 0.15s; }
        .demo-success-btn:hover { filter: brightness(1.07); transform: translateY(-1px); }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) { .stat-chip { display: none; } }
        @media (max-width: 660px) { .demo-modal { padding: 28px 20px 24px; } .d-grid { grid-template-columns: 1fr; } }
        @media (max-width: 600px) {
            header { padding: 0 16px; }
            .header-tagline, .header-badge { display: none; }
            .login-card { padding: 28px 24px; }
            footer { padding: 14px 20px; flex-direction: column; align-items: center; text-align: center; }
            .footer-center { display: none; }
        }
    </style>
</head>
<body>

<div class="finance-canvas" id="canvas"></div>

<div class="ticker-wrap">
    <div class="ticker-tape">
        <span class="up">VAT ▲ 15.0%</span><span class="sep">  ·  </span>
        <span class="up">CORP TAX ▲ 24.72%</span><span class="sep">  ·  </span>
        <span class="down">PAYE ▼ 0.12%</span><span class="sep">  ·  </span>
        <span class="up">ZWL/USD ▲ 361.90</span><span class="sep">  ·  </span>
        <span class="up">REVENUE ▲ +8.4%</span><span class="sep">  ·  </span>
        <span class="down">DEFICIT ▼ 2.1%</span><span class="sep">  ·  </span>
        <span class="up">COMPLIANCE 98.6%</span><span class="sep">  ·  </span>
        <span class="up">LEAFLIGHT v2.4 ●</span><span class="sep">  ·  </span>
        <span class="up">VAT ▲ 15.0%</span><span class="sep">  ·  </span>
        <span class="up">CORP TAX ▲ 24.72%</span><span class="sep">  ·  </span>
        <span class="down">PAYE ▼ 0.12%</span><span class="sep">  ·  </span>
        <span class="up">ZWL/USD ▲ 361.90</span><span class="sep">  ·  </span>
        <span class="up">REVENUE ▲ +8.4%</span><span class="sep">  ·  </span>
        <span class="down">DEFICIT ▼ 2.1%</span><span class="sep">  ·  </span>
        <span class="up">COMPLIANCE 98.6%</span><span class="sep">  ·  </span>
        <span class="up">LEAFLIGHT v2.4 ●</span><span class="sep">  ·  </span>
    </div>
</div>

<div class="page-wrapper">

    <!-- ══ HEADER ══ -->
    <header>
        @php
            $companyNameParts = explode(' ', config('company.name'), 2);
        @endphp
        <a class="header-brand" href="#">
            <div class="header-logo-box"><img src="{{ asset('logo.png') }}" alt="{{ config('company.name') }}" style="width:40px;height:40px;object-fit:contain;"></div>
            <span class="header-name">{{ $companyNameParts[0] }} <span>{{ $companyNameParts[1] ?? '' }}</span></span>
        </a>
        <div class="header-right">
            <span class="header-tagline">Pharmaceutical Wholesale ERP</span>
            <span class="header-badge">
                <span class="live-dot"></span> Live
            </span>
        </div>
    </header>

    <!-- ══ MAIN ══ -->
    <main>
        <div class="login-container">
            <div class="card-glow"></div>

            <div class="stat-chip left">
                <div class="chip-label">TAX COLLECTED</div>
                <div class="chip-val">$4.28M</div>
                <div class="chip-delta">▲ 12.4% this month</div>
            </div>
            <div class="stat-chip right">
                <div class="chip-label">COMPLIANCE</div>
                <div class="chip-val">98.6%</div>
                <div class="chip-delta">▲ 0.3% vs last yr</div>
            </div>

            <div class="login-card">

                @if (session('status'))
                    <div class="status-alert">{{ session('status') }}</div>
                @endif

                <div class="card-header">
                    <div class="card-logo-wrap">
                        <div class="card-logo"><img src="{{ asset('logo.png') }}" alt="{{ config('company.name') }}" style="width:88px;height:88px;object-fit:contain;"></div>
                    </div>
                    <div class="card-title">Welcome back</div>
                    <div class="card-subtitle">Sign in to your {{ config('company.name') }} account</div>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrap">
                            <span class="input-icon">✉</span>
                            <input type="email" id="email" name="email"
                                   value="{{ old('email') }}"
                                   required autofocus autocomplete="username">
                        </div>
                        @error('email')
                            <div class="input-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="password" name="password"
                                   required autocomplete="current-password">
                        </div>
                        @error('password')
                            <div class="input-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn-login">Sign In →</button>

                    <div class="divider"></div>

                </form>
            </div>
        </div>
    </main>

    <!-- ══ FOOTER ══ -->
    <footer>
        <div class="footer-left">
            &copy; <strong>{{ config('company.name') }}</strong> {{ date('Y') }}. All rights reserved.
            @if (config('company.address'))
                <span class="d-none d-md-inline"> &nbsp;·&nbsp; {{ config('company.address') }}</span>
            @endif
            @if (config('company.phone_sales'))
                <span class="d-none d-md-inline"> &nbsp;·&nbsp; {{ config('company.phone_sales') }}</span>
            @endif
            @if (config('company.email_sales'))
                <span class="d-none d-md-inline"> &nbsp;·&nbsp; {{ config('company.email_sales') }}</span>
            @endif
        </div>
        <div class="footer-center">
            <a href="{{ route('privacy') }}">Privacy Policy</a>
            <a href="{{ route('terms') }}">Terms of Service</a>
            <a href="{{ route('support') }}">Support</a>
        </div>
        <div class="footer-right">
            <span class="live-dot"></span>
            All systems operational
        </div>
    </footer>

</div>

<!-- ══════════════════════════════════════════════
     BOOK A DEMO MODAL
══════════════════════════════════════════════ -->
<div class="demo-overlay" id="demoOverlay" onclick="overlayClick(event)">
    <div class="demo-modal" id="demoModal">

        <button class="demo-close" onclick="closeDemo()" title="Close">✕</button>

        <!-- header -->
        <div class="demo-hd">
            <div class="demo-hd-logo-wrap">
                <div class="demo-hd-logo"><img src="{{ asset('logo.png') }}" alt="Leaf Light" style="width:68px;height:68px;object-fit:contain;"></div>
            </div>
            <div class="demo-hd-title">Book a Free Demo</div>
            <div class="demo-hd-sub">Schedule a personalised Leaf Light Systems walkthrough with our team</div>
            <div class="demo-badges">
                <span class="demo-badge">🕐 30-min session</span>
                <span class="demo-badge">📊 Live dashboard</span>
                <span class="demo-badge">🤝 1-on-1 guided tour</span>
            </div>
        </div>

        <!-- FORM -->
        <form id="demoForm" onsubmit="submitDemo(event)" novalidate>

            <!-- Company Details -->
            <div class="demo-sec">Company Details</div>

            <div class="d-grid">
                <div class="d-field">
                    <label>Company Name <span class="req">*</span></label>
                    <div class="d-wrap"><span class="d-ico">🏢</span>
                        <input type="text" id="f_company" placeholder="Acme Corporation">
                    </div>
                    <div class="d-errmsg" id="e_company"></div>
                </div>
                <div class="d-field">
                    <label>Company Email <span class="req">*</span></label>
                    <div class="d-wrap"><span class="d-ico">✉</span>
                        <input type="email" id="f_email" placeholder="info@company.co.zw">
                    </div>
                    <div class="d-errmsg" id="e_email"></div>
                </div>
            </div>

            <div class="d-grid full" style="margin-top:14px">
                <div class="d-field">
                    <label>Company Address <span class="req">*</span></label>
                    <div class="d-wrap"><span class="d-ico">📍</span>
                        <input type="text" id="f_address" placeholder="123 Samora Machel Ave, Harare">
                    </div>
                    <div class="d-errmsg" id="e_address"></div>
                </div>
            </div>

            <!-- Contact Person -->
            <div class="demo-sec">Contact Person</div>

            <div class="d-grid">
                <div class="d-field">
                    <label>Contact Person Name <span class="req">*</span></label>
                    <div class="d-wrap"><span class="d-ico">👤</span>
                        <input type="text" id="f_contact" placeholder="Jane Smith">
                    </div>
                    <div class="d-errmsg" id="e_contact"></div>
                </div>
                <div class="d-field">
                    <label>Contact Person Phone <span class="req">*</span></label>
                    <div class="d-wrap"><span class="d-ico">📱</span>
                        <input type="tel" id="f_contact_phone" placeholder="+263 77 123 4567">
                    </div>
                    <div class="d-errmsg" id="e_contact_phone"></div>
                </div>
            </div>

            <!-- Phone Numbers -->
            <div class="demo-sec">Phone Numbers</div>

            <div class="d-grid">
                <div class="d-field">
                    <label>Primary Phone Number <span class="req">*</span></label>
                    <div class="d-wrap"><span class="d-ico">☎</span>
                        <input type="tel" id="f_primary" placeholder="+263 24 123 4567">
                    </div>
                    <div class="d-errmsg" id="e_primary"></div>
                </div>
                <div class="d-field">
                    <label>WhatsApp Number <span class="req">*</span></label>
                    <div class="d-wrap"><span class="d-ico">💬</span>
                        <input type="tel" id="f_whatsapp" placeholder="+263 78 123 4567">
                    </div>
                    <div class="d-errmsg" id="e_whatsapp"></div>
                </div>
            </div>

            <!-- Schedule -->
            <div class="demo-sec">Preferred Demo Schedule</div>

            <div class="d-grid">
                <div class="d-field">
                    <label>Preferred Date <span class="req">*</span></label>
                    <div class="d-wrap"><span class="d-ico">📅</span>
                        <input type="date" id="f_date">
                    </div>
                    <div class="d-errmsg" id="e_date"></div>
                </div>
                <div class="d-field">
                    <label>Preferred Time <span class="req">*</span></label>
                    <div class="d-wrap"><span class="d-ico">🕐</span>
                        <input type="time" id="f_time" min="08:00" max="19:00">
                    </div>
                    <div class="d-hint">Available: 8:00 AM – 7:00 PM</div>
                    <div class="d-errmsg" id="e_time"></div>
                </div>
            </div>

            <!-- Calendar status feedback -->
            <div class="cal-status" id="calStatus"></div>

            <button type="submit" class="demo-submit" id="demoSubmitBtn">
                <div class="d-spinner" id="demoSpinner"></div>
                <span id="demoSubmitTxt">📅 Book Demo & Add to Calendar</span>
            </button>

        </form>

        <!-- SUCCESS STATE -->
        <div class="demo-success" id="demoSuccess">
            <div class="demo-success-icon">✅</div>
            <div class="demo-success-title">Demo Booked!</div>
            <div class="demo-success-msg">
                Your demo has been confirmed.<br>
                Check your email for the Google Calendar invite with a 15-minute reminder.
            </div>
            <div class="demo-success-detail" id="successDetail">
                📅 Loading event details…
            </div>
            <button class="demo-success-btn" onclick="closeDemo()">Done</button>
        </div>

    </div>
</div>


<script>
/* ── floating symbols ── */
const symbols = ['$','%','£','€','¢','TAX','VAT','PAYE','ROI','▲','▼','◆','12%','15%','24%','+4.2','-0.8','+12.4','2,450','18,900'];
const canvas  = document.getElementById('canvas');
for (let i = 0; i < 30; i++) {
    const el = document.createElement('div');
    el.classList.add('symbol');
    el.textContent = symbols[Math.floor(Math.random() * symbols.length)];
    el.style.cssText = `left:${Math.random()*100}%;bottom:-60px;font-size:${Math.random()*9+10}px;animation-duration:${Math.random()*18+14}s;animation-delay:${Math.random()*22}s;`;
    canvas.appendChild(el);
}

/* ════════════════════════
   MODAL CONTROLS
════════════════════════ */
function openDemo() {
    document.getElementById('demoOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
    document.getElementById('f_date').min = new Date().toISOString().split('T')[0];
}

function closeDemo() {
    document.getElementById('demoOverlay').classList.remove('open');
    document.body.style.overflow = '';
    setTimeout(resetDemo, 300);
}

function overlayClick(e) {
    if (e.target === document.getElementById('demoOverlay')) closeDemo();
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDemo(); });

function resetDemo() {
    document.getElementById('demoForm').style.display    = '';
    document.getElementById('demoSuccess').style.display = 'none';
    document.getElementById('demoForm').reset();
    document.getElementById('demoSubmitBtn').disabled      = false;
    document.getElementById('demoSpinner').style.display   = 'none';
    document.getElementById('demoSubmitTxt').style.display = 'inline';
    document.querySelectorAll('#demoForm input').forEach(i => i.classList.remove('d-err','d-ok'));
    document.querySelectorAll('.d-errmsg').forEach(el => el.textContent = '');
    setCalStatus('', '', false);
}

/* ════════════════════════
   CALENDAR STATUS HELPER
════════════════════════ */
function setCalStatus(type, msg, show = true) {
    const el = document.getElementById('calStatus');
    el.className = 'cal-status' + (show ? ' show ' + type : '');
    el.textContent = msg;
}

/* ════════════════════════
   VALIDATION RULES
════════════════════════ */
const isPhone  = v => /^[+\d][\d\s\-(). ]{5,18}$/.test(v.trim());
const isEmail  = v => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim());
const notEmpty = v => v.trim() !== '';

const RULES = {
    f_company:       { fn: notEmpty,  msg: 'Company name is required.'          },
    f_email:         { fn: isEmail,   msg: 'Enter a valid email address.'        },
    f_address:       { fn: notEmpty,  msg: 'Address is required.'                },
    f_contact:       { fn: notEmpty,  msg: 'Contact person name is required.'    },
    f_contact_phone: { fn: isPhone,   msg: 'Enter a valid phone number.'         },
    f_primary:       { fn: isPhone,   msg: 'Enter a valid primary phone number.' },
    f_whatsapp:      { fn: isPhone,   msg: 'Enter a valid WhatsApp number.'      },
    f_date: {
        fn: v => { if (!v) return false; return new Date(v) >= new Date(new Date().toDateString()); },
        msg: 'Please select today or a future date.'
    },
    f_time: {
        fn: v => {
            if (!v) return false;
            const [h, m] = v.split(':').map(Number);
            return (h * 60 + m) >= 480 && (h * 60 + m) <= 1140;
        },
        msg: 'Time must be between 8:00 AM and 7:00 PM.'
    },
};

function validateOne(id) {
    const inp   = document.getElementById(id);
    const errEl = document.getElementById(id.replace('f_','e_'));
    const pass  = RULES[id].fn(inp.value);
    inp.classList.toggle('d-err', !pass);
    inp.classList.toggle('d-ok',  pass && inp.value.trim() !== '');
    if (errEl) errEl.textContent = pass ? '' : RULES[id].msg;
    return pass;
}

Object.keys(RULES).forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('blur',   () => validateOne(id));
    el.addEventListener('input',  () => { if (document.getElementById(id.replace('f_','e_'))?.textContent) validateOne(id); });
    el.addEventListener('change', () => validateOne(id));
});

/* ════════════════════════
   WHATSAPP MESSAGE
════════════════════════ */
function fmt12(t) {
    const [h, m] = t.split(':').map(Number);
    return `${h > 12 ? h-12 : (h === 0 ? 12 : h)}:${String(m).padStart(2,'0')} ${h >= 12 ? 'PM' : 'AM'}`;
}
function fmtDate(d) {
    return new Date(d + 'T12:00:00').toLocaleDateString('en-GB',
        { weekday:'long', day:'numeric', month:'long', year:'numeric' });
}
function buildMessage(d) {
    return `🗓️ *LEAF LIGHT SYSTEMS DEMO BOOKING REQUEST*\n\n` +
           `🏢 *Company Name:*       ${d.company}\n` +
           `📍 *Address:*            ${d.address}\n` +
           `✉️  *Company Email:*      ${d.email}\n\n` +
           `👤 *Contact Person:*     ${d.contact}\n` +
           `📱 *Contact Phone:*      ${d.contact_phone}\n` +
           `☎️  *Primary Phone:*      ${d.primary}\n` +
           `💬 *WhatsApp Number:*    ${d.whatsapp}\n\n` +
           `📅 *Preferred Date:*     ${fmtDate(d.date)}\n` +
           `🕐 *Preferred Time:*     ${fmt12(d.time)}\n\n` +
           `_Sent from Leaf Light Systems Login Portal_`;
}

/* ════════════════════════
   SUBMIT HANDLER
════════════════════════ */
function submitDemo(e) {
    e.preventDefault();

    let allOk = true;
    Object.keys(RULES).forEach(id => { if (!validateOne(id)) allOk = false; });

    if (!allOk) {
        document.querySelector('#demoForm input.d-err')
            ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    const data = {
        company:       document.getElementById('f_company').value.trim(),
        email:         document.getElementById('f_email').value.trim(),
        address:       document.getElementById('f_address').value.trim(),
        contact:       document.getElementById('f_contact').value.trim(),
        contact_phone: document.getElementById('f_contact_phone').value.trim(),
        primary:       document.getElementById('f_primary').value.trim(),
        whatsapp:      document.getElementById('f_whatsapp').value.trim(),
        date:          document.getElementById('f_date').value,
        time:          document.getElementById('f_time').value,
    };

    /* show spinner */
    const btn = document.getElementById('demoSubmitBtn');
    btn.disabled = true;
    document.getElementById('demoSpinner').style.display   = 'block';
    document.getElementById('demoSubmitTxt').style.display = 'none';
    setCalStatus('cal-loading', '🗓️ Adding to Google Calendar…');

    /* POST to Laravel backend */
    fetch('{{ route("demo.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            setCalStatus('cal-ok', '✅ Calendar event created — invite sent to ' + data.email);

            /* update success detail */
            document.getElementById('successDetail').textContent =
                '📅 ' + fmtDate(data.date) + ' at ' + fmt12(data.time) + ' · 30 min';

            /* open WhatsApp too */
            setTimeout(() => {
                window.open(`https://wa.me/263787780405?text=${encodeURIComponent(buildMessage(data))}`, '_blank');
                document.getElementById('demoForm').style.display    = 'none';
                document.getElementById('demoSuccess').style.display = 'block';
            }, 900);

        } else {
            /* calendar failed — still open WhatsApp */
            setCalStatus('cal-fail', '⚠️ Calendar unavailable — booking sent via WhatsApp only.');
            setTimeout(() => {
                window.open(`https://wa.me/263787780405?text=${encodeURIComponent(buildMessage(data))}`, '_blank');
                document.getElementById('demoForm').style.display    = 'none';
                document.getElementById('demoSuccess').style.display = 'block';
            }, 1200);
        }
    })
    .catch(() => {
        /* network error — still open WhatsApp */
        setCalStatus('cal-fail', '⚠️ Could not reach server — booking sent via WhatsApp only.');
        setTimeout(() => {
            window.open(`https://wa.me/263787780405?text=${encodeURIComponent(buildMessage(data))}`, '_blank');
            document.getElementById('demoForm').style.display    = 'none';
            document.getElementById('demoSuccess').style.display = 'block';
        }, 1200);
    });
}
</script>

</body>
</html>