<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockBatchController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\GoodsReceivedNoteController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\SalesCreditNoteController;
use App\Http\Controllers\SalesPaymentController;
use App\Http\Controllers\CustomerStatementController;

// ─────────────────────────────────────────────────────────────
// PUBLIC
// ─────────────────────────────────────────────────────────────

Route::get('/', fn() => view('auth.login'));

Route::get('/support', fn() => view('support'))->name('support');
Route::get('/privacy', fn() => view('privacy'))->name('privacy');
Route::get('/terms',   fn() => view('terms'))->name('terms');

Route::post('/book-demo', [DemoController::class, 'store'])->name('demo.store');

// ─────────────────────────────────────────────────────────────
// GOOGLE OAUTH
// ─────────────────────────────────────────────────────────────

Route::get('/auth/google',          [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

// ─────────────────────────────────────────────────────────────
// AUTHENTICATED
// ─────────────────────────────────────────────────────────────

Route::middleware(['auth', 'verified', 'password.fresh'])->group(function () {

    // ── Dashboard ──
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Clients ──
    Route::get('/clients/search', [ClientController::class, 'search'])->name('clients.search');
    Route::resource('clients', ClientController::class)->except(['destroy']);

    // ── Profile ──
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Account settings ──
    Route::get('/account/settings',            [UserController::class, 'settings'])->name('account.settings');
    Route::post('/users',                      [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/data',           [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}/admin-update',   [UserController::class, 'adminUpdate'])->name('users.admin-update');
    Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::put('/users/{id}',                  [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}',               [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/account/photo',              [UserController::class, 'updatePhoto'])->name('account.photo.update');
    Route::delete('/account/photo',            [UserController::class, 'removePhoto'])->name('account.photo.remove');

    // ── Stock Transfers ──
    Route::prefix('stock/transfers')->name('stock.transfers.')->group(function () {
        Route::get('/',                [StockTransferController::class, 'index'])->name('index');
        Route::get('/create',          [StockTransferController::class, 'create'])->name('create');
        Route::post('/',               [StockTransferController::class, 'store'])->name('store');
        Route::get('/template',        [StockTransferController::class, 'template'])->name('template');
        Route::post('/import',         [StockTransferController::class, 'import'])->name('import');
        Route::get('/import/preview',  [StockTransferController::class, 'importPreview'])->name('import.preview');
        Route::post('/import/confirm', [StockTransferController::class, 'importConfirm'])->name('import.confirm');
        Route::get('/audit',           [StockTransferController::class, 'audit'])->name('audit');
        Route::get('/{transfer}',      [StockTransferController::class, 'show'])->name('show');
        Route::get('/{transfer}/export',  [StockTransferController::class, 'export'])->name('export');
        Route::post('/{transfer}/approve', [StockTransferController::class, 'approve'])->name('approve');
        Route::post('/{transfer}/reject',  [StockTransferController::class, 'reject'])->name('reject');
        Route::post('/{transfer}/cancel',  [StockTransferController::class, 'cancel'])->name('cancel');
    });

    // ── Stock Adjustments (stock-take / damage / theft / breakage) ──
    Route::resource('stock-adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/stock-adjustments/{stockAdjustment}/pdf', [StockAdjustmentController::class, 'pdf'])->name('stock-adjustments.pdf');
    Route::post('/stock-adjustments/{stockAdjustment}/approve', [StockAdjustmentController::class, 'approve'])->name('stock-adjustments.approve');
    Route::post('/stock-adjustments/{stockAdjustment}/reject',  [StockAdjustmentController::class, 'reject'])->name('stock-adjustments.reject');

    // ── Products ──
    Route::get('/products/template',    [ProductController::class, 'downloadTemplate'])->name('products.template');
    Route::post('/products/bulk-import', [ProductController::class, 'bulkImport'])->name('products.bulk-import');
    Route::resource('products', ProductController::class);
    Route::post('/stock-batches/{batch}/release', [StockBatchController::class, 'release'])->name('stock-batches.release');

    // ── Suppliers & Procurement ──
    Route::resource('suppliers', SupplierController::class)->except(['destroy']);
    Route::post('/suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');
    Route::post('/purchase-orders/generate-drafts', [PurchaseOrderController::class, 'generateDrafts'])->name('purchase-orders.generate-drafts');
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::post('/purchase-orders/{purchaseOrder}/submit',  [PurchaseOrderController::class, 'submit'])->name('purchase-orders.submit');
    Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::post('/purchase-orders/{purchaseOrder}/close',   [PurchaseOrderController::class, 'close'])->name('purchase-orders.close');
    Route::resource('goods-received-notes', GoodsReceivedNoteController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/goods-received-notes/{goodsReceivedNote}/pdf', [GoodsReceivedNoteController::class, 'pdf'])->name('goods-received-notes.pdf');

    // ── Sales orders, quotations & FEFO picking ──
    Route::resource('quotations', QuotationController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'convert'])->name('quotations.convert');
    Route::get('/quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('quotations.pdf');

    Route::resource('sales-orders', SalesOrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/sales-orders/{salesOrder}/confirm',       [SalesOrderController::class, 'confirm'])->name('sales-orders.confirm');
    Route::post('/sales-orders/{salesOrder}/start-picking', [SalesOrderController::class, 'startPicking'])->name('sales-orders.start-picking');
    Route::get('/sales-orders/{salesOrder}/picking-list',   [SalesOrderController::class, 'pickingList'])->name('sales-orders.picking-list');
    Route::post('/sales-orders/{salesOrder}/dispatch',      [SalesOrderController::class, 'dispatch'])->name('sales-orders.dispatch');
    Route::post('/sales-orders/{salesOrder}/cancel',        [SalesOrderController::class, 'cancel'])->name('sales-orders.cancel');
    Route::post('/sales-orders/{salesOrder}/return',        [SalesOrderController::class, 'returnItem'])->name('sales-orders.return');

    // ── Invoicing & payment tracking ──
    Route::resource('sales-invoices', SalesInvoiceController::class)->only(['index', 'show']);
    Route::get('/sales-invoices/{salesInvoice}/pdf', [SalesInvoiceController::class, 'pdf'])->name('sales-invoices.pdf');
    Route::post('/sales-invoices/{salesInvoice}/credit-notes', [SalesCreditNoteController::class, 'store'])->name('sales-invoices.credit-notes.store');
    Route::post('/clients/{client}/payments', [SalesPaymentController::class, 'store'])->name('clients.payments.store');
    Route::get('/clients/{client}/statement', [CustomerStatementController::class, 'show'])->name('clients.statement');

    // ── Expenses ──
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');

    // ── Reports ──
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
});

// ─────────────────────────────────────────────────────────────
// ADMIN
// ─────────────────────────────────────────────────────────────

Route::middleware(['auth', 'admin'])->group(function () {

    // ── News / Notifications ──
    Route::prefix('news')->name('news.')->group(function () {
        Route::get('/',               [NewsController::class, 'index'])->name('index');
        Route::get('/create',         [NewsController::class, 'create'])->name('create');
        Route::post('/',              [NewsController::class, 'store'])->name('store');
        Route::get('/{id}/edit',      [NewsController::class, 'edit'])->name('edit');
        Route::put('/{id}',           [NewsController::class, 'update'])->name('update');
        Route::delete('/{id}',        [NewsController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle',   [NewsController::class, 'togglePublish'])->name('toggle');
    });
    Route::get('/admin/users',       [AdminController::class, 'manageUsers'])->name('admin.users');
    Route::get('/admin/settings',    [AdminController::class, 'settings'])->name('admin.settings');
    Route::get('/user-management',   [AdminController::class, 'showUserManagement'])->name('user.management');
    Route::delete('/admin/users/{id}', [AdminController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/users/{id}/reset-password', [AdminController::class, 'resetPassword'])->name('users.reset-password');

    // ── Analytics ──
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

    // ── Branches ──
    Route::get('/admin/branches',              [BranchController::class, 'index'])->name('admin.branches');
    Route::post('/admin/branches',             [BranchController::class, 'store'])->name('admin.branches.store');
    Route::put('/admin/branches/{branch}',     [BranchController::class, 'update'])->name('admin.branches.update');
    Route::delete('/admin/branches/{branch}',  [BranchController::class, 'destroy'])->name('admin.branches.destroy');
});

require __DIR__.'/auth.php';
