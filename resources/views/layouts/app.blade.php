<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@yield('title', 'POS')</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    {{-- Fonts --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
          integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" />

    {{-- OverlayScrollbars --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
          integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg=" crossorigin="anonymous" />

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI=" crossorigin="anonymous" />

    {{-- AdminLTE --}}
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.css') }}" />

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    {{-- SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />

    <style>
        /* ── Design tokens ──────────────────────────────────────── */
        :root {
            --pos-green:       #198754;
            --pos-green-dk:    #145c2d;
            --pos-green-light: #d1e7dd;
        }

        /* ── Modals ─────────────────────────────────────────────── */
        .modal-lg   { max-width: 80%; }
        .modal-body { max-height: 70vh; overflow-y: auto; }

        /* ── Sidebar ────────────────────────────────────────────── */
        .app-sidebar {
            border-right: 1px solid #e9ecef;
        }

        .sidebar-wrapper {
            height: calc(100vh - 3.5rem);
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-logo img            { transition: transform .2s ease; }
        .sidebar-logo img:hover      { transform: scale(1.05); }

        /* nav links default */
        .sidebar-menu .nav-link {
            color: #3d5a3d !important;
            border-left: 3px solid transparent;
            transition: background .15s, border-color .15s;
        }

        .sidebar-menu .nav-link:hover {
            background: var(--pos-green-light) !important;
            border-left: 3px solid var(--pos-green);
        }

        .sidebar-menu .nav-link:hover .nav-icon,
        .sidebar-menu .nav-link:hover p {
            color: var(--pos-green-dk) !important;
        }

        /* active link */
        .sidebar-menu .nav-link.active-page {
            background: var(--pos-green-light) !important;
            border-left: 3px solid var(--pos-green);
        }

        .sidebar-menu .nav-link.active-page .nav-icon,
        .sidebar-menu .nav-link.active-page p {
            color: var(--pos-green-dk) !important;
            font-weight: 600;
        }

        /* ── Flash alerts ───────────────────────────────────────── */
        .flash-alerts {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1060;
            min-width: 300px;
            max-width: 420px;
        }

        /* ── Navbar clock ───────────────────────────────────────── */
        #current-date-time {
            font-size: .85rem;
            font-weight: 600;
            color: #495057;
        }

        /* ── Live-search dropdown ───────────────────────────────── */
        #searchResults {
            position: absolute;
            z-index: 1050;
            width: 100%;
            max-height: 260px;
            overflow-y: auto;
        }
        #searchResults .list-group-item:hover {
            background-color: var(--pos-green-light);
            cursor: pointer;
        }

        /* ══════════════════════════════════════════════════════════
           Shared Pharma ERP page design system
           (page-header, stat cards, filter bar, table card, badges)
           Reused across products/suppliers/purchase-orders/GRNs/
           quotations/sales-orders/invoices/statements.
        ═══════════════════════════════════════════════════════════ */
        .page-wrap   { padding: 1.5rem 2rem 3rem; }
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: .75rem; }
        .page-header h4 { font-weight: 700; color: var(--pos-green-dk); margin: 0; font-size: 1.35rem; letter-spacing: -.01em; }
        .page-header .sub { font-size: .8rem; color: #6c757d; margin-top: .1rem; }

        .stat-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 1rem; margin-bottom: 1.75rem; }
        .stat-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; padding: 1rem 1.25rem; display: flex; align-items: center; gap: .9rem; box-shadow: 0 1px 4px rgba(0,0,0,.05); transition: box-shadow .15s, transform .15s; }
        .stat-card:hover { box-shadow: 0 4px 14px rgba(25,135,84,.12); transform: translateY(-2px); }
        .stat-card .icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
        .stat-card .icon.green  { background: var(--pos-green-light); color: var(--pos-green); }
        .stat-card .icon.blue   { background: #dbeafe; color: #1d4ed8; }
        .stat-card .icon.yellow { background: #fef9c3; color: #a16207; }
        .stat-card .icon.red    { background: #fee2e2; color: #b91c1c; }
        .stat-card .label { font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; color: #6c757d; font-weight: 600; }
        .stat-card .value { font-size: 1.3rem; font-weight: 700; color: #212529; line-height: 1.1; margin-top: .15rem; }

        .filter-bar { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; padding: .9rem 1.25rem; margin-bottom: 1.25rem; display: flex; flex-wrap: wrap; gap: .6rem; align-items: center; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
        .filter-bar .form-control, .filter-bar .form-select { border-radius: 8px; font-size: .82rem; border-color: #dee2e6; height: 36px; }
        .filter-bar .form-control:focus, .filter-bar .form-select:focus { border-color: var(--pos-green); box-shadow: 0 0 0 .2rem rgba(25,135,84,.15); }
        .filter-bar .btn { height: 36px; font-size: .82rem; border-radius: 8px; padding: 0 1rem; }

        .table-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.05); }
        .table-card .table { margin: 0; font-size: .83rem; }
        .table-card .table thead th { background: #f8f9fa; border-bottom: 2px solid #e9ecef; color: #495057; font-weight: 700; font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; padding: .75rem 1rem; white-space: nowrap; }
        .table-card .table tbody td { padding: .65rem 1rem; vertical-align: middle; border-color: #f1f3f5; color: #343a40; }
        .table-card .table tbody tr:hover { background: #f8fffe; }

        .form-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,.05); }
        .form-card .form-label { font-size: .78rem; font-weight: 600; color: #495057; }
        .form-card .form-control, .form-card .form-select { border-radius: 8px; border-color: #dee2e6; }
        .form-card .form-control:focus, .form-card .form-select:focus { border-color: var(--pos-green); box-shadow: 0 0 0 .2rem rgba(25,135,84,.15); }
        .form-section-title { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: #adb5bd; margin: 1.5rem 0 .85rem; padding-bottom: .5rem; border-bottom: 1px solid #f1f3f5; }
        .form-section-title:first-child { margin-top: 0; }

        .badge-status { font-size: .7rem; font-weight: 600; padding: .28em .65em; border-radius: 6px; display: inline-block; }
        .badge-draft, .badge-unpaid       { background: #e9ecef; color: #495057; }
        .badge-pending, .badge-submitted, .badge-picking, .badge-partially_paid { background: #fff3cd; color: #a16207; }
        .badge-approved, .badge-active, .badge-confirmed, .badge-received, .badge-dispatched, .badge-invoiced, .badge-completed, .badge-paid, .badge-accepted { background: #d1e7dd; color: #145c2d; }
        .badge-rejected, .badge-cancelled, .badge-quarantine, .badge-inactive, .badge-expired, .badge-overdue { background: #fee2e2; color: #b91c1c; }
        .badge-closed, .badge-converted  { background: #dbeafe; color: #1d4ed8; }

        .inv-no { font-family: 'Courier New', monospace; font-size: .8rem; color: #495057; font-weight: 600; }
        .user-pill { display: inline-flex; align-items: center; gap: .35rem; font-size: .78rem; color: #495057; }
        .user-pill .avatar { width: 22px; height: 22px; border-radius: 50%; background: var(--pos-green-light); color: var(--pos-green-dk); font-size: .65rem; font-weight: 700; display: flex; align-items: center; justify-content: center; text-transform: uppercase; flex-shrink: 0; }
        .user-pill-avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--pos-green-light); color: var(--pos-green-dk); font-size: .85rem; font-weight: 700; display: flex; align-items: center; justify-content: center; text-transform: uppercase; flex-shrink: 0; }

        .btn-action { width: 30px; height: 30px; border-radius: 7px; border: 1px solid #dee2e6; background: #fff; color: #495057; display: inline-flex; align-items: center; justify-content: center; font-size: .85rem; transition: background .12s, color .12s, border-color .12s; text-decoration: none; cursor: pointer; }
        .btn-action:hover { background: var(--pos-green-light); color: var(--pos-green-dk); border-color: var(--pos-green); }

        .empty-state { text-align: center; padding: 4rem 2rem; color: #adb5bd; }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }
        .empty-state p { font-size: .9rem; margin: 0; }

        .detail-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; padding: 1.25rem 1.4rem; box-shadow: 0 1px 4px rgba(0,0,0,.05); margin-bottom: 1.25rem; }
        .detail-card .card-title { font-size: .75rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: #495057; margin-bottom: 1rem; }
        .detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; }
        .detail-grid .label { font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; color: #adb5bd; font-weight: 700; }
        .detail-grid .value { font-size: .92rem; color: #212529; font-weight: 600; margin-top: .15rem; }

        .pagination .page-link { font-size: .8rem; border-radius: 7px !important; margin: 0 2px; color: var(--pos-green); border-color: #dee2e6; }
        .pagination .page-item.active .page-link { background: var(--pos-green); border-color: var(--pos-green); color: #fff; }
    </style>

    @stack('styles')
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

    {{-- ═══════════════════════════════════
         TOP NAVBAR
    ════════════════════════════════════ --}}
    <nav class="app-header navbar navbar-expand bg-body shadow-sm">
        <div class="container-fluid">

            {{-- Sidebar toggle --}}
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                        <i class="bi bi-list fs-5 text-success"></i>
                    </a>
                </li>
            </ul>

            {{-- Centre brand --}}
            <div class="mx-auto text-center d-none d-md-block">
                <h6 class="mb-0 fw-bold text-success">
                    <i class=""></i> LeafLight Systems
                </h6>
            </div>

            {{-- Right: logged-in user + live clock --}}
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item d-none d-md-block me-3">
                    <span class="nav-link pe-none" id="current-date-time"></span>
                </li>
                <li class="nav-item">
                    <div class="d-flex align-items-center gap-2">
                        <div class="user-pill-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                        <div class="d-none d-sm-block lh-sm">
                            <div class="fw-semibold" style="font-size:.85rem;color:#212529;">{{ auth()->user()->name }}</div>
                            <div class="text-muted" style="font-size:.72rem;">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                </li>
            </ul>

        </div>
    </nav>

    {{-- ═══════════════════════════════════
         SIDEBAR
    ════════════════════════════════════ --}}
    <aside class="app-sidebar bg-white shadow" data-bs-theme="light">
        <div class="sidebar-wrapper" data-overlayscrollbars-initialize>
            <nav class="mt-2">

                {{-- Logo --}}
                <div class="sidebar-logo text-center py-3">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('logo.png') }}" alt="Logo"
                             class="img-fluid rounded-circle" style="max-height: 90px;">
                    </a>
                </div>

                <ul class="nav sidebar-menu flex-column"
                    data-lte-toggle="treeview" role="menu" data-accordion="false">

                    {{-- Dashboard --}}
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}"
                           class="nav-link {{ request()->routeIs('dashboard') ? 'active-page' : '' }}">
                            <i class="nav-icon bi bi-house-door-fill text-success"></i>
                            <p>Home</p>
                        </a>
                    </li>

                    {{-- ═══════ Master Data ═══════ --}}
                    <li class="nav-header text-uppercase small text-muted px-3 pt-2">Master Data</li>

                    <li class="nav-item">
                        <a href="{{ route('products.index') }}"
                           class="nav-link {{ request()->routeIs('products.*') ? 'active-page' : '' }}">
                            <i class="nav-icon bi bi-capsule text-success"></i>
                            <p>Products</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('suppliers.index') }}"
                           class="nav-link {{ request()->routeIs('suppliers.*') ? 'active-page' : '' }}">
                            <i class="nav-icon bi bi-truck text-success"></i>
                            <p>Suppliers</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('clients.index') }}"
                           class="nav-link {{ request()->routeIs('clients.*') ? 'active-page' : '' }}">
                            <i class="nav-icon bi bi-people text-success"></i>
                            <p>Clients</p>
                        </a>
                    </li>

                    {{-- ═══════ Procurement ═══════ --}}
                    <li class="nav-header text-uppercase small text-muted px-3 pt-2">Procurement</li>

                    <li class="nav-item">
                        <a href="{{ route('purchase-orders.index') }}"
                           class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active-page' : '' }}">
                            <i class="nav-icon bi bi-file-earmark-text text-success"></i>
                            <p>Purchase Orders</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('goods-received-notes.index') }}"
                           class="nav-link {{ request()->routeIs('goods-received-notes.*') ? 'active-page' : '' }}">
                            <i class="nav-icon bi bi-clipboard-check text-success"></i>
                            <p>Goods Received</p>
                        </a>
                    </li>

                    {{-- ═══════ Sales ═══════ --}}
                    <li class="nav-header text-uppercase small text-muted px-3 pt-2">Sales</li>

                    <li class="nav-item">
                        <a href="{{ route('quotations.index') }}"
                           class="nav-link {{ request()->routeIs('quotations.*') ? 'active-page' : '' }}">
                            <i class="nav-icon bi bi-file-earmark-ruled text-success"></i>
                            <p>Quotations</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('sales-orders.index') }}"
                           class="nav-link {{ request()->routeIs('sales-orders.*') ? 'active-page' : '' }}">
                            <i class="nav-icon bi bi-cart-plus text-success"></i>
                            <p>Sales Orders</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('sales-invoices.index') }}"
                           class="nav-link {{ request()->routeIs('sales-invoices.*') ? 'active-page' : '' }}">
                            <i class="nav-icon bi bi-receipt-cutoff text-success"></i>
                            <p>Invoices</p>
                        </a>
                    </li>

                    {{-- ═══════ Inventory ═══════ --}}
                    <li class="nav-header text-uppercase small text-muted px-3 pt-2">Inventory</li>

                    <li class="nav-item">
                        <a href="{{ route('stock.transfers.index') }}"
                           class="nav-link {{ request()->routeIs('stock.transfers.*') ? 'active-page' : '' }}">
                            <i class="nav-icon bi bi-arrow-left-right text-success"></i>
                            <p>Stock Transfers</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('stock-adjustments.index') }}"
                           class="nav-link {{ request()->routeIs('stock-adjustments.*') ? 'active-page' : '' }}">
                            <i class="nav-icon bi bi-clipboard-data text-success"></i>
                            <p>Stock Adjustments</p>
                        </a>
                    </li>

                    {{-- ═══════ Insights ═══════ --}}
                    <li class="nav-header text-uppercase small text-muted px-3 pt-2">Insights</li>

                    <li class="nav-item">
                        <a href="{{ route('reports.index') }}"
                           class="nav-link {{ request()->routeIs('reports.*') ? 'active-page' : '' }}">
                            <i class="nav-icon bi bi-file-earmark-bar-graph text-success"></i>
                            <p>Reports</p>
                        </a>
                    </li>
                    @if(auth()->user()?->isAdmin())
                    <li class="nav-item">
                        <a href="{{ route('analytics') }}"
                           class="nav-link {{ request()->routeIs('analytics') ? 'active-page' : '' }}">
                            <i class="nav-icon bi bi-bar-chart-line-fill text-success"></i>
                            <p>Analytics</p>
                        </a>
                    </li>
                    @endif

                    {{-- ═══════ Account ═══════ --}}
                    <li class="nav-header text-uppercase small text-muted px-3 pt-2">Account</li>

                    <li class="nav-item">
                        <a href="{{ route('account.settings') }}"
                           class="nav-link {{ request()->routeIs('account.settings') ? 'active-page' : '' }}">
                            <i class="nav-icon bi bi-person-circle text-success"></i>
                            <p>Profile</p>
                        </a>
                    </li>

                    {{-- Sign Out --}}
                    <li class="nav-item">
                        <a href="{{ route('logout') }}" class="nav-link"
                           onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                            <i class="nav-icon bi bi-box-arrow-left text-success"></i>
                            <p>Sign Out</p>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}"
                              method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>

                </ul>
            </nav>
        </div>
    </aside>

    {{-- ═══════════════════════════════════
         VALIDATION ERRORS (persistent — SweetAlert2 toast handles success/error flashes below)
    ════════════════════════════════════ --}}
    @if($errors->any())
        <div class="flash-alerts">
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-x-octagon-fill me-2"></i>
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════
         PAGE CONTENT
    ════════════════════════════════════ --}}
    <main class="app-main">
        @yield('content')
    </main>

</div>{{-- /.app-wrapper --}}


{{-- ═══════════════════════════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════════════════════════════ --}}

{{-- jQuery --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

{{-- OverlayScrollbars --}}
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"
        integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ="
        crossorigin="anonymous"></script>

{{-- Popper --}}
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
        crossorigin="anonymous"></script>

{{-- AdminLTE --}}
<script src="{{ asset('dist/js/adminlte.js') }}"></script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

{{-- Axios --}}
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
// ── Live clock ────────────────────────────────────────────────
(function () {
    const el = document.getElementById('current-date-time');
    function tick() {
        const d = new Date();
        el.textContent = d.toLocaleDateString('en-US', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        }) + ' – ' + d.toLocaleTimeString();
    }
    tick();
    setInterval(tick, 1000);
})();

// ── Auto-dismiss validation-error alerts after 8 s ─────────────
setTimeout(() => {
    document.querySelectorAll('.flash-alerts .alert').forEach(el => {
        bootstrap.Alert.getOrCreateInstance(el)?.close();
    });
}, 8000);

// ── System action toasts (success / error flash messages) ─────
(function () {
    const toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3800,
        timerProgressBar: true,
        didOpen: (el) => {
            el.addEventListener('mouseenter', Swal.stopTimer);
            el.addEventListener('mouseleave', Swal.resumeTimer);
        },
    });

    @if(session('success'))
        toast.fire({ icon: 'success', title: @json(session('success')) });
    @endif

    @if(session('error'))
        toast.fire({ icon: 'error', title: @json(session('error')) });
    @endif
})();

// ── Confirm-before-submit for destructive/critical actions ─────
// Add data-confirm="Message" to any <form> or <button type="submit">
// and the action pauses for a SweetAlert2 confirmation first.
document.addEventListener('submit', function (e) {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;

    const trigger = form.matches('[data-confirm]')
        ? form
        : (document.activeElement && document.activeElement.closest('[data-confirm]'));
    if (!trigger || trigger.dataset.confirmed === 'true') return;

    e.preventDefault();

    Swal.fire({
        title: trigger.dataset.confirmTitle || 'Are you sure?',
        text: trigger.dataset.confirm,
        icon: trigger.dataset.confirmIcon || 'warning',
        showCancelButton: true,
        confirmButtonText: trigger.dataset.confirmButton || 'Yes, continue',
        cancelButtonText: 'Cancel',
        confirmButtonColor: trigger.dataset.confirmDanger === 'true' ? '#dc3545' : '#198754',
    }).then((result) => {
        if (result.isConfirmed) {
            trigger.dataset.confirmed = 'true';
            form.submit();
        }
    });
}, true);

// ── Financial report: show/hide user select ───────────────────
document.addEventListener('DOMContentLoaded', function () {
    const sel     = document.getElementById('reportTypeSelect');
    const wrapper = document.getElementById('userSelectWrapper');
    if (!sel || !wrapper) return;

    const toggle = () => {
        const perUser = sel.value === 'sales_user' || sel.value === 'tax_user';
        wrapper.classList.toggle('d-none', !perUser);
    };
    toggle();
    sel.addEventListener('change', toggle);
});

// ── Stock modal: tax calculation ──────────────────────────────
function updateStockTax() {
    const code  = document.getElementById('stockTaxCode')?.value;
    const qty   = parseFloat(document.getElementById('stockQuantity')?.value) || 0;
    const price = parseFloat(document.getElementById('sellingPrice')?.value)  || 0;
    const total = price * qty;

    let taxId = '', taxPct = 0, taxAmt = 0;
    if      (code === 'A') { taxId = 1; }
    else if (code === 'B') { taxId = 2; }
    else if (code === 'C') { taxId = 3; taxPct = 15; taxAmt = +(total * 15 / 115).toFixed(2); }

    document.getElementById('stockTaxPercentage').value      = code === 'C' ? '15%' : '0%';
    document.getElementById('hiddenTaxPercentage').value     = taxPct;
    document.getElementById('stockTaxId').value              = taxId;
    document.getElementById('stockTaxAmount').value          = taxAmt;
    document.getElementById('stockSalesAmountWithTax').value = total.toFixed(2);
}

function clearStockForm() {
    document.getElementById('stockForm').reset();
    document.getElementById('stockTaxPercentage').value = '';
}
</script>

{{-- ── Global session expiry / auth handler ──────────────────────────
     Wraps window.fetch once, app-wide. Every AJAX call in every view
     gets 419/401 handling for free — no per-page code needed.
────────────────────────────────────────────────────────────────── --}}
<script>
(function () {
    let sessionAlertShown = false;

    const _fetch = window.fetch;
    window.fetch = async function (...args) {
        const response = await _fetch(...args);

        if (response.status === 419 && !sessionAlertShown) {
            sessionAlertShown = true;
            Swal.fire({
                icon: 'warning',
                title: 'Session Expired',
                text: 'Your session has expired. The page will reload so you can continue.',
                confirmButtonText: 'Reload Now',
                confirmButtonColor: '#198754',
                allowOutsideClick: false,
                allowEscapeKey: false,
            }).then(() => window.location.reload());
        }

        if (response.status === 401 && !sessionAlertShown) {
            sessionAlertShown = true;
            Swal.fire({
                icon: 'error',
                title: 'Logged Out',
                text: 'You have been logged out. You will be redirected to the login page.',
                confirmButtonText: 'Go to Login',
                confirmButtonColor: '#198754',
                allowOutsideClick: false,
                allowEscapeKey: false,
            }).then(() => window.location.href = '{{ route("login") }}');
        }

        return response;
    };
})();
</script>

{{-- Page-specific scripts injected here --}}
@stack('scripts')
</body>
</html>