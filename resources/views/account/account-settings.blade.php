@extends('layouts.app')

@section('title', 'Account Settings')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* ── Tokens ─────────────────────────────────────────────────── */
    :root {
        --g:       #16a34a;
        --g-dk:    #14532d;
        --g-lt:    #f0fdf4;
        --g-mid:   #bbf7d0;
        --g-ring:  #86efac;
        --red:     #dc2626;
        --amber:   #d97706;
        --bg:      #f4f7f4;
        --surf:    #ffffff;
        --border:  #d1e8d8;
        --text:    #1c2b1c;
        --muted:   #6b7a6b;
        --display: 'Playfair Display', serif;
        --sans:    'Nunito', sans-serif;
        --rad:     12px;
        --sh:      0 2px 12px rgba(22,163,74,.09);
        --sh-lg:   0 8px 32px rgba(22,163,74,.13);
    }

    /* ── Page ───────────────────────────────────────────────────── */
    .ac-page {
        font-family: var(--sans);
        background: var(--bg);
        min-height: 100vh;
        padding: 2.5rem 2rem;
        color: var(--text);
    }

    /* ── Header ─────────────────────────────────────────────────── */
    .ac-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .ac-header h1 {
        font-family: var(--display);
        font-size: 2.1rem;
        font-weight: 800;
        margin: 0;
        color: var(--text);
        line-height: 1.1;
    }
    .ac-header h1 span { color: var(--g); }
    .ac-header p {
        margin: .3rem 0 0;
        font-size: .82rem;
        color: var(--muted);
    }

    /* ── Layout grid ─────────────────────────────────────────────── */
    .ac-grid {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 1.5rem;
        align-items: start;
    }
    @media(max-width: 860px) { .ac-grid { grid-template-columns: 1fr; } }

    /* ── Card ────────────────────────────────────────────────────── */
    .ac-card {
        background: var(--surf);
        border: 1.5px solid var(--border);
        border-radius: var(--rad);
        box-shadow: var(--sh);
        overflow: hidden;
        animation: fadeUp .35s ease both;
    }
    .ac-card:nth-child(2) { animation-delay: .07s; }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Avatar card ─────────────────────────────────────────────── */
    .ac-avatar-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 2rem 1.5rem;
        text-align: center;
        gap: 1rem;
    }
    .ac-avatar {
        width: 96px; height: 96px;
        border-radius: 50%;
        background: var(--g);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--display);
        font-size: 2.2rem;
        font-weight: 700;
        color: #fff;
        border: 4px solid var(--g-mid);
        box-shadow: 0 0 0 6px var(--g-lt);
        flex-shrink: 0;
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }
    .ac-avatar img {
        width: 100%; height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }
    /* Photo upload overlay */
    .ac-avatar-overlay {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: rgba(0,0,0,.52);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        opacity: 0;
        transition: opacity .18s ease;
        pointer-events: none;
    }
    .ac-avatar-overlay i   { color: #fff; font-size: 1.2rem; }
    .ac-avatar-overlay span { color: #fff; font-size: .58rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    .ac-avatar:hover .ac-avatar-overlay { opacity: 1; }
    /* Photo action links */
    .ac-photo-actions {
        display: flex;
        gap: .65rem;
        align-items: center;
        flex-wrap: wrap;
        justify-content: center;
    }
    .ac-photo-link {
        font-size: .75rem;
        font-weight: 700;
        cursor: pointer;
        border: none;
        background: transparent;
        padding: 0;
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        transition: opacity .15s;
        text-decoration: none;
    }
    .ac-photo-link:hover { opacity: .7; }
    .ac-photo-link--green { color: var(--g); }
    .ac-photo-link--red   { color: var(--red); }
    .ac-avatar-name {
        font-family: var(--display);
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text);
        margin: 0;
    }
    .ac-avatar-email {
        font-size: .8rem;
        color: var(--muted);
        margin: 0;
        word-break: break-all;
    }

    /* ── User type badge ─────────────────────────────────────────── */
    .ac-role-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .3rem .85rem;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .ac-role-badge.admin { background: #fef3c7; color: #92400e; border: 1.5px solid #fcd34d; }
    .ac-role-badge.user  { background: var(--g-lt); color: var(--g-dk); border: 1.5px solid var(--g-ring); }

    /* ── Info rows in avatar card ─────────────────────────────────── */
    .ac-meta {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: .5rem;
        border-top: 1.5px solid var(--border);
        padding-top: 1rem;
        margin-top: .25rem;
    }
    .ac-meta-row {
        display: flex;
        align-items: center;
        gap: .6rem;
        font-size: .8rem;
        color: var(--muted);
    }
    .ac-meta-row i { color: var(--g); font-size: .95rem; flex-shrink: 0; }
    .ac-meta-row span { word-break: break-all; }

    /* ── Danger zone inside avatar card ──────────────────────────── */
    .ac-danger-zone {
        width: 100%;
        border-top: 1.5px dashed #fca5a5;
        padding-top: 1rem;
        margin-top: .25rem;
    }
    .ac-danger-zone p {
        font-size: .75rem;
        color: var(--red);
        margin: 0 0 .6rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    /* ── Section tabs ────────────────────────────────────────────── */
    .ac-tabs {
        display: flex;
        gap: 0;
        border-bottom: 2px solid var(--border);
        margin-bottom: 1.5rem;
    }
    .ac-tab {
        font-family: var(--sans);
        font-weight: 700;
        font-size: .83rem;
        padding: .65rem 1.2rem;
        border: none;
        background: transparent;
        color: var(--muted);
        cursor: pointer;
        border-bottom: 2.5px solid transparent;
        margin-bottom: -2px;
        transition: .15s;
        letter-spacing: .02em;
        display: flex;
        align-items: center;
        gap: .4rem;
    }
    .ac-tab.active, .ac-tab:hover { color: var(--g); }
    .ac-tab.active { border-bottom-color: var(--g); }
    .ac-tab-panel { display: none; }
    .ac-tab-panel.active { display: block; }

    /* ── Form fields ─────────────────────────────────────────────── */
    .ac-field { margin-bottom: 1.25rem; }
    .ac-label {
        display: block;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--muted);
        margin-bottom: .35rem;
    }
    .ac-input {
        width: 100%;
        padding: .6rem .9rem;
        border: 1.5px solid var(--border);
        border-radius: 9px;
        font-family: var(--sans);
        font-size: .9rem;
        color: var(--text);
        background: var(--bg);
        transition: border-color .15s, box-shadow .15s;
        outline: none;
    }
    .ac-input:focus {
        border-color: var(--g);
        box-shadow: 0 0 0 3px rgba(22,163,74,.12);
        background: #fff;
    }
    .ac-input.is-invalid { border-color: var(--red); }
    .ac-input-err {
        font-size: .76rem;
        color: var(--red);
        margin-top: .3rem;
    }
    .ac-input-hint {
        font-size: .76rem;
        color: var(--muted);
        margin-top: .3rem;
    }

    /* ── Password strength bar ───────────────────────────────────── */
    .pw-bar-wrap {
        height: 4px;
        background: var(--border);
        border-radius: 999px;
        margin-top: .5rem;
        overflow: hidden;
    }
    .pw-bar {
        height: 100%;
        border-radius: 999px;
        transition: width .3s ease, background .3s ease;
        width: 0%;
    }

    /* ── Submit row ──────────────────────────────────────────────── */
    .ac-submit-row {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: .75rem;
        padding-top: 1rem;
        border-top: 1.5px solid var(--border);
        margin-top: .5rem;
    }

    /* ── Buttons ─────────────────────────────────────────────────── */
    .ac-btn {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .6rem 1.25rem;
        border-radius: 9px;
        font-family: var(--sans);
        font-size: .85rem;
        font-weight: 700;
        border: 1.5px solid transparent;
        cursor: pointer;
        transition: .15s ease;
        text-decoration: none;
        white-space: nowrap;
    }
    .ac-btn:active { transform: scale(.97); }
    .ac-btn--green { background: var(--g);    color: #fff; border-color: var(--g); }
    .ac-btn--green:hover { background: var(--g-dk); color: #fff; }
    .ac-btn--ghost { background: transparent; color: var(--g); border-color: var(--g); }
    .ac-btn--ghost:hover { background: var(--g-lt); }
    .ac-btn--red   { background: var(--red);  color: #fff; border-color: var(--red); }
    .ac-btn--red:hover   { background: #b91c1c; }
    .ac-btn--dark  { background: var(--text); color: #fff; border-color: var(--text); }

    /* ── Card header ─────────────────────────────────────────────── */
    .ac-card-head {
        padding: 1rem 1.5rem;
        background: var(--g-lt);
        border-bottom: 1.5px solid var(--border);
        font-family: var(--display);
        font-weight: 700;
        font-size: 1rem;
        color: var(--g-dk);
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .ac-card-body { padding: 1.5rem; }

    /* ── Google integration row ──────────────────────────────────── */
    .ac-integration {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .9rem 1rem;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        background: var(--bg);
        margin-bottom: .75rem;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .ac-integration__left {
        display: flex;
        align-items: center;
        gap: .75rem;
    }
    .ac-integration__icon {
        width: 38px; height: 38px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
        background: #fff;
        border: 1.5px solid var(--border);
        flex-shrink: 0;
    }
    .ac-integration__name {
        font-weight: 700;
        font-size: .88rem;
        color: var(--text);
    }
    .ac-integration__sub {
        font-size: .75rem;
        color: var(--muted);
        margin-top: .1rem;
    }
    .ac-integration__status {
        font-size: .72rem;
        font-weight: 700;
        padding: .2rem .6rem;
        border-radius: 999px;
    }
    .ac-integration__status.linked   { background: var(--g-lt); color: var(--g-dk); }
    .ac-integration__status.unlinked { background: #f3f4f6; color: var(--muted); }

    /* ── Users table ────────────────────────────────────────────── */
    .users-search {
        width: 100%;
        padding: .55rem .9rem .55rem 2.4rem;
        border: 1.5px solid var(--border);
        border-radius: 9px;
        font-family: var(--sans);
        font-size: .85rem;
        color: var(--text);
        background: var(--bg);
        outline: none;
        transition: border-color .15s, box-shadow .15s;
        margin-bottom: 1.1rem;
    }
    .users-search:focus { border-color: var(--g); box-shadow: 0 0 0 3px rgba(22,163,74,.12); background: #fff; }
    .users-search-wrap { position: relative; }
    .users-search-wrap i { position: absolute; left: .8rem; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: .9rem; pointer-events: none; }

    .users-table { width: 100%; border-collapse: collapse; font-size: .83rem; }
    .users-table thead th {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .07em;
        font-weight: 700;
        color: var(--muted);
        padding: .5rem .75rem;
        border-bottom: 1.5px solid var(--border);
        background: var(--g-lt);
        white-space: nowrap;
    }
    .users-table tbody tr { border-bottom: 1px solid var(--border); transition: background .1s; }
    .users-table tbody tr:last-child { border-bottom: none; }
    .users-table tbody tr:hover { background: var(--g-lt); }
    .users-table td { padding: .65rem .75rem; vertical-align: middle; }

    .u-pill { display: inline-flex; align-items: center; gap: .6rem; }
    .u-avatar {
        width: 30px; height: 30px;
        border-radius: 50%;
        background: var(--g);
        color: #fff;
        font-size: .75rem;
        font-weight: 700;
        font-family: var(--display);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .u-avatar.admin-av { background: #d97706; }
    .u-name  { font-weight: 700; color: var(--text); font-size: .84rem; }
    .u-email { font-size: .75rem; color: var(--muted); }
    .u-you   { font-size: .68rem; font-weight: 700; background: var(--g-lt); color: var(--g-dk); border: 1px solid var(--g-ring); border-radius: 999px; padding: .1rem .5rem; margin-left: .35rem; vertical-align: middle; }

    .users-empty { text-align: center; padding: 2.5rem 1rem; color: var(--muted); font-size: .85rem; }

    /* ── Status + action badges ──────────────────────────────────── */
    .u-status {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .7rem; font-weight: 700; padding: .25em .65em;
        border-radius: 999px; white-space: nowrap;
    }
    .u-status.active   { background: var(--g-lt);  color: var(--g-dk);  border: 1.5px solid var(--g-ring); }
    .u-status.inactive { background: #f3f4f6;       color: var(--muted); border: 1.5px solid #d1d5db; }

    .u-btn {
        width: 28px; height: 28px; border-radius: 7px;
        border: 1.5px solid var(--border);
        background: var(--surf); color: var(--muted);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .8rem; cursor: pointer; transition: .12s ease;
        line-height: 1;
    }
    .u-btn:hover.edit-btn    { background: #dbeafe; color: #1d4ed8; border-color: #93c5fd; }
    .u-btn:hover.toggle-btn  { background: var(--g-lt); color: var(--g);   border-color: var(--g-ring); }
    .u-btn.deactivate:hover  { background: #fee2e2; color: var(--red);  border-color: #fca5a5; }

    /* ── Add-user collapsible form ───────────────────────────────── */
    .add-user-toggle {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .45rem 1rem;
        border-radius: 9px;
        font-family: var(--sans);
        font-size: .8rem;
        font-weight: 700;
        background: var(--g);
        color: #fff;
        border: none;
        cursor: pointer;
        transition: background .15s;
        margin-bottom: 1rem;
    }
    .add-user-toggle:hover { background: var(--g-dk); }
    .add-user-toggle[aria-expanded="true"] { background: var(--muted); }

    .add-user-form {
        border: 1.5px solid var(--border);
        border-radius: 10px;
        padding: 1.25rem;
        background: var(--g-lt);
        margin-bottom: 1.1rem;
        animation: fadeUp .2s ease both;
    }
    .add-user-form .form-title {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--g-dk);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: .4rem;
    }
    .add-user-form .row-fields {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .85rem;
    }
    @media(max-width:580px) { .add-user-form .row-fields { grid-template-columns: 1fr; } }
    .add-user-form .ac-label { color: var(--g-dk); }
    .add-user-form .ac-input { background: #fff; }
    .add-user-form .form-footer {
        display: flex;
        justify-content: flex-end;
        gap: .6rem;
        padding-top: .85rem;
        margin-top: .85rem;
        border-top: 1.5px solid var(--border);
    }

    /* ── Toast ───────────────────────────────────────────────────── */
    #ac-toasts {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: .5rem;
    }
    .ac-toast {
        padding: .65rem 1rem;
        border-radius: 9px;
        font-size: .83rem;
        font-family: var(--sans);
        font-weight: 600;
        display: flex; align-items: center; gap: .5rem;
        min-width: 220px;
        box-shadow: var(--sh-lg);
        animation: slideIn .22s ease;
    }
    @keyframes slideIn {
        from { transform: translateX(30px); opacity: 0; }
        to   { transform: translateX(0);    opacity: 1; }
    }
    .ac-toast.success { background: var(--g);   color: #fff; }
    .ac-toast.error   { background: var(--red); color: #fff; }
</style>
@endpush

@section('content')
<div class="ac-page">

    {{-- ── Header ─────────────────────────────────────────────── --}}
    <div class="ac-header">
        <div>
            <h1>Account <span>Settings</span></h1>
            <p>Manage your profile, password and integrations</p>
        </div>
        <a href="{{ route('dashboard') }}" class="ac-btn ac-btn--dark">
            <i class="bi bi-arrow-left-short fs-5"></i>Dashboard
        </a>
    </div>

    <div class="ac-grid">

        {{-- ════ LEFT: Profile snapshot ════ --}}
        <div class="ac-card">
            <div class="ac-avatar-card">

                {{-- Avatar with photo upload --}}
                <input type="file" id="photoFileInput" accept="image/jpeg,image/png,image/gif,image/webp"
                       style="display:none;" onchange="uploadPhoto(this)">

                <div class="ac-avatar" id="avatarCircle" onclick="document.getElementById('photoFileInput').click()"
                     title="Click to change photo">
                    @if(optional($user)->profile_photo_path)
                        <img src="{{ $user->profilePhotoUrl() }}" alt="{{ $user->name }}" id="avatarImg">
                    @else
                        <span id="avatarInitial">{{ strtoupper(substr(optional($user)->name ?? 'U', 0, 1)) }}</span>
                    @endif
                    <div class="ac-avatar-overlay">
                        <i class="bi bi-camera-fill"></i>
                        <span>Change</span>
                    </div>
                </div>

                {{-- Photo action links --}}
                <div class="ac-photo-actions" id="photoActions">
                    <button type="button" class="ac-photo-link ac-photo-link--green"
                            onclick="document.getElementById('photoFileInput').click()">
                        <i class="bi bi-upload"></i>Upload Photo
                    </button>
                    @if(optional($user)->profile_photo_path)
                    <span style="color:var(--border)">|</span>
                    <button type="button" class="ac-photo-link ac-photo-link--red" id="removePhotoBtn"
                            onclick="removePhoto()">
                        <i class="bi bi-trash3"></i>Remove
                    </button>
                    @endif
                </div>

                <div>
                    <p class="ac-avatar-name">{{ optional($user)->name ?? 'Unknown User' }}</p>
                    <p class="ac-avatar-email">{{ optional($user)->email ?? '—' }}</p>
                </div>

                {{-- Role badge --}}
                @if(optional($user)->user_type == 1)
                    <span class="ac-role-badge admin">
                        <i class="bi bi-shield-fill-check"></i>Admin
                    </span>
                @elseif(optional($user)->user_type == 2)
                    <span class="ac-role-badge admin">
                        <i class="bi bi-shield-fill-check"></i>Supervisor
                    </span>
                @else
                    <span class="ac-role-badge user">
                        <i class="bi bi-person-fill"></i>Cashier
                    </span>
                @endif

                {{-- Meta info --}}
                <div class="ac-meta">
                    <div class="ac-meta-row">
                        <i class="bi bi-calendar3"></i>
                        <span>Joined {{ optional($user)->created_at?->format('M d, Y') ?? '—' }}</span>
                    </div>
                    <div class="ac-meta-row">
                        <i class="bi bi-clock-history"></i>
                        <span>Updated {{ optional($user)->updated_at?->diffForHumans() ?? '—' }}</span>
                    </div>
                    @if(optional($user)->google_id)
                    <div class="ac-meta-row">
                        <i class="bi bi-google"></i>
                        <span>Google linked</span>
                    </div>
                    @endif
                </div>

                {{-- Danger zone --}}
                <div class="ac-danger-zone">
                    <p><i class="bi bi-exclamation-triangle me-1"></i>Danger Zone</p>
                    <button type="button" class="ac-btn ac-btn--red w-100"
                            style="justify-content:center;font-size:.8rem"
                            data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        <i class="bi bi-person-x-fill"></i>Delete My Account
                    </button>
                </div>

            </div>
        </div>

        {{-- ════ RIGHT: Tabbed form ════ --}}
        <div class="ac-card">
            <div class="ac-card-head">
                <i class="bi bi-gear-fill"></i>Edit Settings
            </div>
            <div class="ac-card-body">

                {{-- Tabs --}}
                <div class="ac-tabs">
                    <button class="ac-tab active" data-tab="tab-profile">
                        <i class="bi bi-person"></i>Profile
                    </button>
                    <button class="ac-tab" data-tab="tab-password">
                        <i class="bi bi-lock"></i>Password
                    </button>
                    <button class="ac-tab" data-tab="tab-integrations">
                        <i class="bi bi-plug"></i>Integrations
                    </button>
                    <button class="ac-tab" data-tab="tab-users">
                        <i class="bi bi-people"></i>Users
                        @if(isset($users))
                        <span style="background:var(--g);color:#fff;font-size:.65rem;font-weight:700;padding:.1rem .45rem;border-radius:999px;margin-left:.2rem;">{{ $users->count() }}</span>
                        @endif
                    </button>
                </div>

                {{-- ── Tab: Profile ── --}}
                <div class="ac-tab-panel active" id="tab-profile">
                    <form method="POST"
                          action="{{ $user ? route('users.update', $user->id) : '#' }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_tab" value="profile">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="ac-field">
                                    <label class="ac-label">Full Name *</label>
                                    <input type="text" name="name"
                                           class="ac-input @error('name') is-invalid @enderror"
                                           value="{{ old('name', optional($user)->name) }}"
                                           placeholder="Your full name" required>
                                    @error('name')
                                        <p class="ac-input-err"><i class="bi bi-x-circle me-1"></i>{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="ac-field">
                                    <label class="ac-label">Email Address *</label>
                                    <input type="email" name="email"
                                           class="ac-input @error('email') is-invalid @enderror"
                                           value="{{ old('email', optional($user)->email) }}"
                                           placeholder="you@example.com" required>
                                    @error('email')
                                        <p class="ac-input-err"><i class="bi bi-x-circle me-1"></i>{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="ac-field">
                                    <label class="ac-label">User Type</label>
                                    <select name="user_type"
                                            class="ac-input @error('user_type') is-invalid @enderror">
                                        <option value="0" {{ old('user_type', optional($user)->user_type) == 0 ? 'selected' : '' }}>
                                            Cashier
                                        </option>
                                        <option value="1" {{ old('user_type', optional($user)->user_type) == 1 ? 'selected' : '' }}>
                                            Admin
                                        </option>
                                        <option value="2" {{ old('user_type', optional($user)->user_type) == 2 ? 'selected' : '' }}>
                                            Supervisor
                                        </option>
                                    </select>
                                    @error('user_type')
                                        <p class="ac-input-err">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="ac-submit-row">
                            <button type="reset" class="ac-btn ac-btn--ghost">
                                <i class="bi bi-arrow-counterclockwise"></i>Reset
                            </button>
                            <button type="submit" class="ac-btn ac-btn--green">
                                <i class="bi bi-save2"></i>Save Profile
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ── Tab: Password ── --}}
                <div class="ac-tab-panel" id="tab-password">
                    <form method="POST"
                          action="{{ $user ? route('users.update', $user->id) : '#' }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_tab" value="password">

                        <div class="ac-field">
                            <label class="ac-label">New Password</label>
                            <input type="password" name="password"
                                   id="pwInput"
                                   class="ac-input @error('password') is-invalid @enderror"
                                   placeholder="Leave blank to keep current"
                                   autocomplete="new-password"
                                   oninput="checkPwStrength(this.value)">
                            <div class="pw-bar-wrap">
                                <div class="pw-bar" id="pwBar"></div>
                            </div>
                            <p class="ac-input-hint" id="pwHint">
                                Min 8 characters recommended.
                            </p>
                            @error('password')
                                <p class="ac-input-err"><i class="bi bi-x-circle me-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="ac-field">
                            <label class="ac-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation"
                                   id="pwConfirm"
                                   class="ac-input"
                                   placeholder="Repeat new password"
                                   autocomplete="new-password"
                                   oninput="checkPwMatch()">
                            <p class="ac-input-hint" id="pwMatchHint"></p>
                        </div>

                        <div class="ac-submit-row">
                            <button type="submit" class="ac-btn ac-btn--green">
                                <i class="bi bi-lock-fill"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ── Tab: Integrations ── --}}
                <div class="ac-tab-panel" id="tab-integrations">
                    <form method="POST"
                          action="{{ $user ? route('users.update', $user->id) : '#' }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_tab" value="integrations">

                        {{-- Google integration display --}}
                        <div class="ac-integration mb-3">
                            <div class="ac-integration__left">
                                <div class="ac-integration__icon">
                                    <i class="bi bi-google" style="color:#ea4335"></i>
                                </div>
                                <div>
                                    <div class="ac-integration__name">Google Account</div>
                                    <div class="ac-integration__sub">
                                        {{ optional($user)->google_id ? 'Linked to Google OAuth' : 'Not linked' }}
                                    </div>
                                </div>
                            </div>
                            @if(optional($user)->google_id)
                                <span class="ac-integration__status linked">
                                    <i class="bi bi-check-circle-fill me-1"></i>Linked
                                </span>
                            @else
                                <span class="ac-integration__status unlinked">Not linked</span>
                            @endif
                        </div>

                        <div class="ac-field">
                            <label class="ac-label">Google ID</label>
                            <input type="text" name="google_id"
                                   class="ac-input @error('google_id') is-invalid @enderror"
                                   value="{{ old('google_id', optional($user)->google_id) }}"
                                   placeholder="Google OAuth ID">
                            @error('google_id')
                                <p class="ac-input-err">{{ $message }}</p>
                            @enderror
                            <p class="ac-input-hint">Your Google OAuth identifier.</p>
                        </div>

                        <div class="ac-field">
                            <label class="ac-label">Google Token</label>
                            <input type="text" name="google_token"
                                   class="ac-input @error('google_token') is-invalid @enderror"
                                   value="{{ old('google_token', optional($user)->google_token) }}"
                                   placeholder="Google OAuth token">
                            @error('google_token')
                                <p class="ac-input-err">{{ $message }}</p>
                            @enderror
                            <p class="ac-input-hint">Stored OAuth token for Google services.</p>
                        </div>

                        <div class="ac-submit-row">
                            <button type="submit" class="ac-btn ac-btn--green">
                                <i class="bi bi-save2"></i>Save Integrations
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ── Tab: Users ── --}}
                <div class="ac-tab-panel" id="tab-users">

                    {{-- Add user toggle --}}
                    <button type="button" class="add-user-toggle"
                            id="addUserToggle"
                            aria-expanded="false"
                            onclick="toggleAddUser()">
                        <i class="bi bi-person-plus-fill"></i>Add New User
                    </button>

                    {{-- Add user form (collapsed by default) --}}
                    <div class="add-user-form" id="addUserForm" style="display:none;">
                        <div class="form-title"><i class="bi bi-person-plus"></i>New User Account</div>

                        @if($errors->hasAny(['new_name','new_email','new_password','new_user_type']))
                        <div style="background:#fee2e2;border:1.5px solid #fca5a5;border-radius:8px;padding:.65rem .9rem;margin-bottom:.9rem;font-size:.8rem;color:#b91c1c;">
                            <i class="bi bi-exclamation-circle-fill me-1"></i>
                            @foreach(['new_name','new_email','new_password','new_user_type'] as $f)
                                @error($f)<span>{{ $message }}</span><br>@enderror
                            @endforeach
                        </div>
                        @endif

                        <form method="POST" action="{{ route('users.store') }}">
                            @csrf
                            <div class="row-fields">
                                <div class="ac-field">
                                    <label class="ac-label">Full Name <span style="color:var(--red)">*</span></label>
                                    <input type="text" name="new_name"
                                           class="ac-input @error('new_name') is-invalid @enderror"
                                           value="{{ old('new_name') }}"
                                           placeholder="e.g. Blessward Mutsotso" required>
                                </div>
                                <div class="ac-field">
                                    <label class="ac-label">Email Address <span style="color:var(--red)">*</span></label>
                                    <input type="email" name="new_email"
                                           class="ac-input @error('new_email') is-invalid @enderror"
                                           value="{{ old('new_email') }}"
                                           placeholder="e.g. blesswardmutsotso404@gmail.com" required>
                                </div>
                                <div class="ac-field">
                                    <label class="ac-label">Password <span style="color:var(--red)">*</span></label>
                                    <input type="password" name="new_password"
                                           class="ac-input @error('new_password') is-invalid @enderror"
                                           placeholder="Min 10 chars, upper+lower, number, symbol"
                                           autocomplete="new-password" required>
                                </div>
                                <div class="ac-field">
                                    <label class="ac-label">Confirm Password <span style="color:var(--red)">*</span></label>
                                    <input type="password" name="new_password_confirmation"
                                           class="ac-input"
                                           placeholder="Repeat password"
                                           autocomplete="new-password" required>
                                </div>
                                <div class="ac-field">
                                    <label class="ac-label">Access Level <span style="color:var(--red)">*</span></label>
                                    <select name="new_user_type" class="ac-input @error('new_user_type') is-invalid @enderror">
                                        <option value="0" {{ old('new_user_type','0') == '0' ? 'selected' : '' }}>Cashier</option>
                                        <option value="1" {{ old('new_user_type') == '1' ? 'selected' : '' }}>Admin</option>
                                        <option value="2" {{ old('new_user_type') == '2' ? 'selected' : '' }}>Supervisor</option>
                                    </select>
                                </div>
                                <div class="ac-field">
                                    <label class="ac-label">Pharma Role <span style="color:var(--red)">*</span></label>
                                    <select name="new_role" class="ac-input @error('new_role') is-invalid @enderror">
                                        @foreach (\App\Models\User::roles() as $value => $label)
                                            <option value="{{ $value }}" {{ old('new_role') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-footer">
                                <button type="button" class="ac-btn ac-btn--ghost" onclick="toggleAddUser()">
                                    <i class="bi bi-x-lg"></i>Cancel
                                </button>
                                <button type="submit" class="ac-btn ac-btn--green">
                                    <i class="bi bi-person-check-fill"></i>Create User
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="users-search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text" class="users-search" id="usersSearch"
                               placeholder="Search by name or email…"
                               oninput="filterUsers(this.value)">
                    </div>

                    @if(isset($users) && $users->count())
                    <div style="overflow-x:auto;border:1.5px solid var(--border);border-radius:10px;overflow:hidden;">
                        <table class="users-table" id="usersTable">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th style="text-align:center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $u)
                                @php
                                    $isAdmin  = $u->isAdmin();
                                    $isMe     = $u->id === auth()->id();
                                    $isActive = $u->is_active ?? true;
                                    $initial  = strtoupper(substr($u->name ?? 'U', 0, 1));
                                @endphp
                                <tr class="user-row" id="user-row-{{ $u->id }}">
                                    <td>
                                        <div class="u-pill">
                                            <div class="u-avatar {{ $isAdmin ? 'admin-av' : '' }}">{{ $initial }}</div>
                                            <div>
                                                <div class="u-name">
                                                    {{ $u->name }}
                                                    @if($isMe)<span class="u-you">you</span>@endif
                                                </div>
                                                <div class="u-email">{{ $u->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($isAdmin)
                                            <span class="ac-role-badge admin">
                                                <i class="bi bi-shield-fill-check"></i>Admin
                                            </span>
                                        @else
                                            <span class="ac-role-badge user">
                                                <i class="bi bi-person-fill"></i>Cashier
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="u-status {{ $isActive ? 'active' : 'inactive' }}" id="status-badge-{{ $u->id }}">
                                            <i class="bi {{ $isActive ? 'bi-circle-fill' : 'bi-circle' }}" style="font-size:.45rem;"></i>
                                            {{ $isActive ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td style="font-size:.78rem;color:var(--muted);white-space:nowrap;">
                                        {{ $u->created_at?->format('d M Y') ?? '—' }}
                                    </td>
                                    <td style="text-align:center;white-space:nowrap;">
                                        <button type="button"
                                                class="u-btn edit-btn"
                                                title="Edit user"
                                                onclick="openEditModal({{ $u->id }})">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        @if(!$isMe)
                                        <button type="button"
                                                class="u-btn toggle-btn {{ !$isActive ? 'deactivate' : '' }}"
                                                id="toggle-btn-{{ $u->id }}"
                                                title="{{ $isActive ? 'Deactivate' : 'Activate' }}"
                                                onclick="toggleActive({{ $u->id }})">
                                            <i class="bi {{ $isActive ? 'bi-person-slash' : 'bi-person-check' }}" id="toggle-icon-{{ $u->id }}"></i>
                                        </button>
                                        @else
                                        <button type="button" class="u-btn" title="Cannot deactivate yourself" disabled style="opacity:.35;cursor:not-allowed;">
                                            <i class="bi bi-person-slash"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p style="font-size:.75rem;color:var(--muted);margin-top:.65rem;text-align:right;">
                        {{ $users->count() }} {{ Str::plural('user', $users->count()) }} in the system
                    </p>
                    @else
                    <div class="users-empty">
                        <i class="bi bi-people" style="font-size:2.5rem;display:block;margin-bottom:.75rem;"></i>
                        No users found.
                    </div>
                    @endif

                </div>

            </div>{{-- /.ac-card-body --}}
        </div>{{-- /.ac-card --}}

    </div>{{-- /.ac-grid --}}
</div>{{-- /.ac-page --}}

{{-- ── Toast container ─────────────────────────────────────────── --}}
<div id="ac-toasts"></div>

{{-- ── Edit User Modal ──────────────────────────────────────────── --}}
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
            <div class="modal-header border-0" style="background:var(--g-lt);border-bottom:1.5px solid var(--border);">
                <h5 class="modal-title fw-bold" style="color:var(--g-dk);font-family:var(--display);font-size:1.05rem;">
                    <i class="bi bi-person-gear me-2"></i>Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:1.5rem;">
                <div id="editUserLoading" style="text-align:center;padding:2rem;color:var(--muted);font-size:.85rem;">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading…
                </div>
                <form id="editUserForm" style="display:none;">
                    <input type="hidden" id="editUserId">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="ac-field">
                                <label class="ac-label">Full Name <span style="color:var(--red)">*</span></label>
                                <input type="text" id="editUserName" class="ac-input" required placeholder="Full name">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="ac-field">
                                <label class="ac-label">Email Address <span style="color:var(--red)">*</span></label>
                                <input type="email" id="editUserEmail" class="ac-input" required placeholder="user@example.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ac-field">
                                <label class="ac-label">Access Level</label>
                                <select id="editUserType" class="ac-input">
                                    <option value="0">Cashier</option>
                                    <option value="1">Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ac-field">
                                <label class="ac-label">Pharma Role</label>
                                <select id="editUserRole" class="ac-input">
                                    @foreach (\App\Models\User::roles() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ac-field">
                                <label class="ac-label">New Password <span style="color:var(--muted);font-weight:400">(optional)</span></label>
                                <input type="password" id="editUserPassword" class="ac-input"
                                       placeholder="Leave blank to keep current" autocomplete="new-password">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ac-field">
                                <label class="ac-label">Confirm Password</label>
                                <input type="password" id="editUserPasswordConfirm" class="ac-input"
                                       placeholder="Repeat new password" autocomplete="new-password">
                            </div>
                        </div>
                    </div>
                    <div id="editUserError" style="display:none;margin-top:.9rem;background:#fee2e2;border:1.5px solid #fca5a5;border-radius:8px;padding:.6rem .9rem;font-size:.8rem;color:#b91c1c;"></div>
                </form>
            </div>
            <div class="modal-footer border-0" style="padding:.85rem 1.5rem;gap:.5rem;">
                <button type="button" class="ac-btn ac-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="ac-btn ac-btn--green" id="saveUserBtn" onclick="saveEditUser()">
                    <i class="bi bi-save2 me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Delete Account Confirm Modal ────────────────────────────── --}}
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
            <div class="modal-header border-0" style="background:#fee2e2">
                <h5 class="modal-title fw-bold" style="color:#991b1b;font-family:var(--display)">
                    <i class="bi bi-person-x-fill me-2"></i>Delete Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:1.5rem;font-size:.88rem">
                <p class="mb-2">
                    This will <strong>permanently delete</strong> your account and all associated data.
                    This action <strong>cannot be undone</strong>.
                </p>
                <p class="mb-0" style="color:var(--muted)">
                    Type <strong>DELETE</strong> below to confirm.
                </p>
                <input type="text" id="deleteConfirmInput" class="ac-input mt-2"
                       placeholder="Type DELETE to confirm" autocomplete="off">
            </div>
            <div class="modal-footer border-0" style="padding:.85rem 1.5rem;gap:.5rem">
                <button type="button" class="ac-btn ac-btn--ghost"
                        data-bs-dismiss="modal">Cancel</button>
                @if($user)
                <form action="{{ route('users.destroy', $user->id) }}" method="POST" id="deleteAccountForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ac-btn ac-btn--red"
                            id="confirmDeleteBtn" disabled>
                        <i class="bi bi-trash"></i>Delete Account
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Toast helper (global so AJAX callbacks can call it) ────────
function toast(msg, type = 'success') {
    const wrap = document.getElementById('ac-toasts');
    const el   = document.createElement('div');
    const icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill' };
    el.className = `ac-toast ${type}`;
    el.innerHTML = `<i class="bi ${icons[type] ?? icons.success}"></i>${msg}`;
    wrap.appendChild(el);
    setTimeout(() => {
        el.style.opacity = '0'; el.style.transition = '.3s';
        setTimeout(() => el.remove(), 320);
    }, 3500);
}

document.addEventListener('DOMContentLoaded', function () {

    // ── Tabs ───────────────────────────────────────────────────
    document.querySelectorAll('.ac-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.ac-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.ac-tab-panel').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            document.getElementById(this.dataset.tab)?.classList.add('active');
        });
    });

    // ── Open correct tab on load ────────────────────────────────
    @if($errors->has('password') || $errors->has('password_confirmation'))
        document.querySelector('[data-tab="tab-password"]')?.click();
    @elseif($errors->has('google_id') || $errors->has('google_token'))
        document.querySelector('[data-tab="tab-integrations"]')?.click();
    @elseif(session('open_tab') === 'users' || $errors->hasAny(['new_name','new_email','new_password','new_user_type']))
        document.querySelector('[data-tab="tab-users"]')?.click();
        @if($errors->hasAny(['new_name','new_email','new_password','new_user_type']))
            toggleAddUser(true);
        @endif
    @endif

    // ── Avatar initial updates live with name input ────────────
    const nameInput = document.querySelector('input[name="name"]');
    if (nameInput) {
        nameInput.addEventListener('input', function () {
            const initial = this.value.trim().charAt(0).toUpperCase() || '?';
            const span = document.getElementById('avatarInitial');
            if (span) span.textContent = initial;
        });
    }

    // ── Flash toasts ───────────────────────────────────────────
    @if(session('success'))
        toast('{{ session('success') }}', 'success');
    @endif
    @if(session('error'))
        toast('{{ session('error') }}', 'error');
    @endif

    // ── Delete account: require typing DELETE ──────────────────
    const deleteInput   = document.getElementById('deleteConfirmInput');
    const deleteBtn     = document.getElementById('confirmDeleteBtn');
    if (deleteInput && deleteBtn) {
        deleteInput.addEventListener('input', function () {
            deleteBtn.disabled = this.value.trim() !== 'DELETE';
        });
    }
});

// ── Password strength ──────────────────────────────────────────
function checkPwStrength(val) {
    const bar  = document.getElementById('pwBar');
    const hint = document.getElementById('pwHint');
    if (!bar) return;

    let score = 0;
    if (val.length >= 8)                    score++;
    if (/[A-Z]/.test(val))                  score++;
    if (/[0-9]/.test(val))                  score++;
    if (/[^A-Za-z0-9]/.test(val))           score++;

    const levels = [
        { pct: '0%',   bg: 'transparent', label: 'Min 8 characters recommended.' },
        { pct: '25%',  bg: '#dc2626',     label: 'Weak — add uppercase or numbers.' },
        { pct: '50%',  bg: '#d97706',     label: 'Fair — add symbols for more security.' },
        { pct: '75%',  bg: '#2563eb',     label: 'Good — almost there!' },
        { pct: '100%', bg: '#16a34a',     label: 'Strong password!' },
    ];
    const l = val.length === 0 ? levels[0] : levels[score];
    bar.style.width      = l.pct;
    bar.style.background = l.bg;
    if (hint) hint.textContent = l.label;
}

// ── Add-user form toggle ───────────────────────────────────────
function toggleAddUser(forceOpen) {
    const form   = document.getElementById('addUserForm');
    const toggle = document.getElementById('addUserToggle');
    const open   = forceOpen ?? form.style.display === 'none';
    form.style.display = open ? '' : 'none';
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.innerHTML = open
        ? '<i class="bi bi-x-lg"></i>Cancel'
        : '<i class="bi bi-person-plus-fill"></i>Add New User';
    if (open) form.querySelector('input[name="new_name"]')?.focus();
}

// ── Toggle user active/inactive (AJAX) ────────────────────────
async function toggleActive(userId) {
    const btn  = document.getElementById('toggle-btn-' + userId);
    const icon = document.getElementById('toggle-icon-' + userId);
    const badge = document.getElementById('status-badge-' + userId);
    if (btn) btn.disabled = true;

    try {
        const res = await fetch('/users/' + userId + '/toggle-active', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        const data = await res.json();
        if (!res.ok) { toast(data.message || 'An error occurred.', 'error'); return; }

        if (data.is_active) {
            badge?.classList.replace('inactive', 'active');
            badge.innerHTML = '<i class="bi bi-circle-fill" style="font-size:.45rem;"></i> Active';
            icon?.classList.replace('bi-person-check', 'bi-person-slash');
            btn?.setAttribute('title', 'Deactivate');
            btn?.classList.remove('deactivate');
        } else {
            badge?.classList.replace('active', 'inactive');
            badge.innerHTML = '<i class="bi bi-circle" style="font-size:.45rem;"></i> Inactive';
            icon?.classList.replace('bi-person-slash', 'bi-person-check');
            btn?.setAttribute('title', 'Activate');
            btn?.classList.add('deactivate');
        }
        toast(data.message, 'success');
    } catch (e) {
        toast('Network error. Please try again.', 'error');
    } finally {
        if (btn) btn.disabled = false;
    }
}

// ── Edit user modal ────────────────────────────────────────────
function getEditModal() {
    return bootstrap.Modal.getOrCreateInstance(document.getElementById('editUserModal'));
}

async function openEditModal(userId) {
    const editModal = getEditModal();
    document.getElementById('editUserForm').style.display  = 'none';
    document.getElementById('editUserLoading').style.display = '';
    document.getElementById('editUserError').style.display = 'none';
    document.getElementById('editUserPassword').value = '';
    document.getElementById('editUserPasswordConfirm').value = '';
    editModal.show();

    try {
        const res  = await fetch('/users/' + userId + '/data', {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (!res.ok) {
            getEditModal().hide();
            toast(data.message || 'Failed to load user data.', 'error');
            return;
        }

        document.getElementById('editUserId').value    = data.id    ?? '';
        document.getElementById('editUserName').value  = data.name  ?? '';
        document.getElementById('editUserEmail').value = data.email ?? '';
        document.getElementById('editUserType').value  = data.user_type ?? 0;
        document.getElementById('editUserRole').value  = data.role ?? 'sales';
        document.getElementById('editUserLoading').style.display = 'none';
        document.getElementById('editUserForm').style.display    = '';
    } catch (e) {
        getEditModal().hide();
        toast('Failed to load user data.', 'error');
    }
}

async function saveEditUser() {
    const userId = document.getElementById('editUserId').value;
    const pw     = document.getElementById('editUserPassword').value;
    const pwConf = document.getElementById('editUserPasswordConfirm').value;
    const errBox = document.getElementById('editUserError');
    const saveBtn = document.getElementById('saveUserBtn');

    errBox.style.display = 'none';

    if (pw && pw !== pwConf) {
        errBox.textContent   = 'Passwords do not match.';
        errBox.style.display = '';
        return;
    }

    const body = {
        name:      document.getElementById('editUserName').value,
        email:     document.getElementById('editUserEmail').value,
        user_type: document.getElementById('editUserType').value,
        role:      document.getElementById('editUserRole').value,
    };
    if (pw) { body.password = pw; body.password_confirmation = pwConf; }

    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

    try {
        const res  = await fetch('/users/' + userId + '/admin-update', {
            method:  'PUT',
            headers: {
                'Content-Type':  'application/json',
                'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
                'Accept':        'application/json',
            },
            body: JSON.stringify(body),
        });
        const data = await res.json();

        if (!res.ok) {
            const msgs = data.errors
                ? Object.values(data.errors).flat().join(' ')
                : (data.message || 'Could not save changes.');
            errBox.textContent   = msgs;
            errBox.style.display = '';
            return;
        }

        getEditModal().hide();
        toast(data.message, 'success');
        // Patch the row in place without reloading
        const row = document.getElementById('user-row-' + userId);
        if (row) {
            const nameEl  = row.querySelector('.u-name');
            const emailEl = row.querySelector('.u-email');
            if (nameEl)  nameEl.childNodes[0].textContent = body.name + ' ';
            if (emailEl) emailEl.textContent = body.email;
        }
    } catch (e) {
        errBox.textContent   = 'Network error. Please try again.';
        errBox.style.display = '';
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="bi bi-save2 me-1"></i>Save Changes';
    }
}

// ── Users live search ──────────────────────────────────────────
function filterUsers(q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('#usersTable .user-row').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = (!q || text.includes(q)) ? '' : 'none';
    });
}

// ── Password match indicator ───────────────────────────────────
function checkPwMatch() {
    const pw      = document.getElementById('pwInput')?.value;
    const confirm = document.getElementById('pwConfirm')?.value;
    const hint    = document.getElementById('pwMatchHint');
    if (!hint || !confirm) return;
    if (confirm.length === 0) { hint.textContent = ''; return; }
    if (pw === confirm) {
        hint.style.color  = '#16a34a';
        hint.textContent  = '✓ Passwords match';
    } else {
        hint.style.color  = '#dc2626';
        hint.textContent  = '✗ Passwords do not match';
    }
}

// ── Profile photo upload ───────────────────────────────────────
async function uploadPhoto(input) {
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    if (file.size > 3 * 1024 * 1024) {
        toast('Image must be under 3 MB.', 'error');
        input.value = '';
        return;
    }

    const avatar = document.getElementById('avatarCircle');
    avatar.style.opacity = '.5';
    avatar.style.pointerEvents = 'none';

    const formData = new FormData();
    formData.append('photo', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    try {
        const res  = await fetch('{{ route("account.photo.update") }}', { method: 'POST', body: formData });
        const data = await res.json();

        if (!res.ok) {
            toast(data.message || 'Upload failed.', 'error');
            return;
        }

        // Swap avatar to photo
        const initial = document.getElementById('avatarInitial');
        let img = document.getElementById('avatarImg');
        if (!img) {
            if (initial) initial.remove();
            img = document.createElement('img');
            img.id  = 'avatarImg';
            img.alt = 'Profile photo';
            // insert before the overlay div
            avatar.insertBefore(img, avatar.querySelector('.ac-avatar-overlay'));
        }
        img.src = data.url + '?t=' + Date.now();

        // Show remove button if not present
        const actions = document.getElementById('photoActions');
        if (actions && !document.getElementById('removePhotoBtn')) {
            actions.insertAdjacentHTML('beforeend',
                '<span style="color:var(--border)">|</span>' +
                '<button type="button" class="ac-photo-link ac-photo-link--red" id="removePhotoBtn" onclick="removePhoto()">' +
                '<i class="bi bi-trash3"></i>Remove</button>');
        }

        toast(data.message, 'success');
    } catch (e) {
        toast('Network error. Please try again.', 'error');
    } finally {
        avatar.style.opacity = '1';
        avatar.style.pointerEvents = '';
        input.value = '';
    }
}

// ── Profile photo remove ───────────────────────────────────────
async function removePhoto() {
    const btn = document.getElementById('removePhotoBtn');
    if (btn) { btn.disabled = true; btn.style.opacity = '.5'; }

    try {
        const res  = await fetch('{{ route("account.photo.remove") }}', {
            method:  'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept':       'application/json',
            },
        });
        const data = await res.json();

        if (!res.ok) { toast(data.message || 'Could not remove photo.', 'error'); return; }

        // Revert to initial
        const avatar  = document.getElementById('avatarCircle');
        const img     = document.getElementById('avatarImg');
        if (img) img.remove();

        if (!document.getElementById('avatarInitial')) {
            const span  = document.createElement('span');
            span.id     = 'avatarInitial';
            const nameInput = document.querySelector('input[name="name"]');
            span.textContent = (nameInput?.value || '{{ optional($user)->name ?? "U" }}').trim().charAt(0).toUpperCase() || 'U';
            avatar.insertBefore(span, avatar.querySelector('.ac-avatar-overlay'));
        }

        // Remove the separator + remove button
        const actions = document.getElementById('photoActions');
        if (actions) {
            // Remove everything after the Upload button
            const children = Array.from(actions.childNodes);
            children.forEach((c, i) => { if (i > 0) c.remove(); });
        }

        toast(data.message, 'success');
    } catch (e) {
        toast('Network error. Please try again.', 'error');
    } finally {
        if (btn) { btn.disabled = false; btn.style.opacity = '1'; }
    }
}
</script>
@endpush