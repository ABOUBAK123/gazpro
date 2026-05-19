<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\LivreurController;
use App\Http\Controllers\AdminLivreurController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfitController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\MobileApiController;

// Redirect root
Route::get('/', fn() => redirect()->route('login'));

// CinetPay IPN webhook (public, no auth, no CSRF — exempted in bootstrap/app.php)
Route::post('/abonnement/notify', [SubscriptionController::class, 'notify'])->name('subscription.notify');

// Auth routes
Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',    [AuthController::class, 'login']);
Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');

// Livreur mobile app (public, token-based)
Route::get('/livreur/{token}',                             [LivreurController::class, 'mobileApp'])->name('livreur.app');
Route::get('/livreur/{token}/manifest.json',               [LivreurController::class, 'manifest'])->name('livreur.manifest');
Route::patch('/livreur/{token}/position',                  [LivreurController::class, 'updateLocation'])->name('livreur.location');
Route::patch('/livreur/{token}/commandes/{order}/statut',  [LivreurController::class, 'updateDeliveryStatus'])->name('livreur.delivery.update');

// Public client order form
Route::get('/commander',  [OrderController::class, 'clientForm'])->name('client.order');
Route::post('/commander', [OrderController::class, 'clientStore'])->name('client.order.store');

// API for client form (web)
Route::get('/api/stores', [ClientController::class, 'getStoresApi']);
Route::get('/api/stock',  [ClientController::class, 'getStockApi']);

// ─── Mobile App API ───────────────────────────────────────────────────────
Route::prefix('api')->name('api.')->group(function () {
    // Auth (public)
    Route::post('/auth/register', [MobileApiController::class, 'register'])->name('auth.register');
    Route::post('/auth/login',    [MobileApiController::class, 'login'])->name('auth.login');

    // Stores (public)
    Route::get('/stores',        [MobileApiController::class, 'stores'])->name('stores.index');
    Route::get('/stores/{id}',   [MobileApiController::class, 'storeDetail'])->name('stores.show');

    // Orders (public — identified by phone)
    Route::post('/orders',               [MobileApiController::class, 'createOrder'])->name('orders.store');
    Route::get('/orders/client/{phone}', [MobileApiController::class, 'myOrders'])->name('orders.client');
});

// API for real-time alerts widget (authenticated store users)
Route::middleware(\App\Http\Middleware\AuthenticateStore::class)
    ->get('/api/stock-alerts', [ClientController::class, 'stockAlertsApi']);

// Admin routes
Route::middleware(\App\Http\Middleware\AuthenticateAdmin::class)->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard',    [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/inscriptions', [AdminController::class, 'inscriptions'])->name('inscriptions');
    Route::patch('/stores/{store}/approve', [AdminController::class, 'approveStore'])->name('stores.approve');
    Route::patch('/stores/{store}/reject',  [AdminController::class, 'rejectStore'])->name('stores.reject');
    Route::get('/currencies',              [AdminController::class, 'currencies'])->name('currencies');
    Route::post('/currencies',             [AdminController::class, 'storeCurrency'])->name('currencies.store');
    Route::put('/currencies/{currency}',   [AdminController::class, 'updateCurrency'])->name('currencies.update');
    Route::delete('/currencies/{currency}',[AdminController::class, 'deleteCurrency'])->name('currencies.destroy');
    Route::get('/subscription',        [AdminController::class, 'subscriptionSettings'])->name('subscription');
    Route::put('/subscription',        [AdminController::class, 'updateSubscription'])->name('subscription.update');
    Route::post('/subscription/logos', [AdminController::class, 'uploadPaymentLogos'])->name('subscription.logos');

    // Accounts management
    Route::get('/comptes',                     [AccountController::class, 'index'])->name('accounts');
    Route::put('/comptes/stores/{store}',      [AccountController::class, 'updateStore'])->name('accounts.store.update');
    Route::delete('/comptes/stores/{store}',   [AccountController::class, 'destroyStore'])->name('accounts.store.destroy');
    Route::put('/comptes/staff/{staff}',       [AccountController::class, 'updateStaff'])->name('accounts.staff.update');
    Route::delete('/comptes/staff/{staff}',    [AccountController::class, 'destroyStaff'])->name('accounts.staff.destroy');

    // Livreurs (platform-wide)
    Route::get('/livreurs',                        [AdminLivreurController::class, 'index'])->name('livreurs.index');
    Route::post('/livreurs',                       [AdminLivreurController::class, 'store'])->name('livreurs.store');
    Route::put('/livreurs/{livreur}',              [AdminLivreurController::class, 'update'])->name('livreurs.update');
    Route::delete('/livreurs/{livreur}',           [AdminLivreurController::class, 'destroy'])->name('livreurs.destroy');
    Route::post('/livreurs/{livreur}/token',       [AdminLivreurController::class, 'regenerateToken'])->name('livreurs.token');

    // Admin settings
    Route::get('/parametres',              [AdminSettingsController::class, 'index'])->name('settings');
    Route::post('/parametres/brands',      [AdminSettingsController::class, 'addBrand'])->name('settings.brand.add');
    Route::delete('/parametres/brands',    [AdminSettingsController::class, 'deleteBrand'])->name('settings.brand.delete');
    Route::post('/parametres/weights',     [AdminSettingsController::class, 'addWeight'])->name('settings.weight.add');
    Route::delete('/parametres/weights',   [AdminSettingsController::class, 'deleteWeight'])->name('settings.weight.delete');
    Route::post('/parametres/terms',       [AdminSettingsController::class, 'saveTerms'])->name('settings.terms');
    Route::post('/parametres/email',       [AdminSettingsController::class, 'saveEmailConfig'])->name('settings.email');
});

// Store (manager + staff) routes
Route::middleware(\App\Http\Middleware\AuthenticateStore::class)->group(function () {
    Route::get('/dashboard', [StoreController::class, 'dashboard'])->name('store.dashboard');
    Route::get('/dashboard/data', [StoreController::class, 'dashboardData'])->name('store.dashboard.data');

    // Profile
    Route::get('/profil',                  [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profil/parametres',       [ProfileController::class, 'settings'])->name('profile.settings');
    Route::put('/profil/parametres',       [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profil/livraison',        [ProfileController::class, 'deliverySettings'])->name('profile.delivery');
    Route::put('/profil/livraison',        [ProfileController::class, 'updateDelivery'])->name('profile.delivery.update');

    // Profit
    Route::get('/benefices', [ProfitController::class, 'index'])->name('profit.index');

    // Subscription
    Route::get('/abonnement',          [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/abonnement/payer',   [SubscriptionController::class, 'initiate'])->name('subscription.initiate');
    Route::get('/abonnement/retour',   [SubscriptionController::class, 'returnPage'])->name('subscription.return');

    // Stock
    Route::get('/stock',            [StockController::class, 'index'])->name('stock.index');
    Route::post('/stock',           [StockController::class, 'store'])->name('stock.store');
    Route::put('/stock/{stock}',    [StockController::class, 'update'])->name('stock.update');
    Route::delete('/stock/{stock}', [StockController::class, 'destroy'])->name('stock.destroy');

    // Orders
    Route::get('/commandes',                     [OrderController::class, 'index'])->name('orders.index');
    Route::get('/commandes/creer',               [OrderController::class, 'create'])->name('orders.create');
    Route::post('/commandes',                    [OrderController::class, 'store'])->name('orders.store');
    Route::patch('/commandes/{order}/statut',    [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::delete('/commandes/{order}',          [OrderController::class, 'destroy'])->name('orders.destroy');

    // Sales
    Route::get('/ventes',       [SaleController::class, 'index'])->name('sales.index');
    Route::get('/ventes/creer', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/ventes',      [SaleController::class, 'store'])->name('sales.store');

    // Expenses
    Route::get('/depenses',              [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/depenses',             [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/depenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Clients
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');

    // Staff management (manager only)
    Route::get('/personnel',            [StaffController::class, 'index'])->name('staff.index');
    Route::post('/personnel',           [StaffController::class, 'store'])->name('staff.store');
    Route::put('/personnel/{staff}',    [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/personnel/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');

    // Salaries (manager only)
    Route::get('/salaires',                      [SalaryController::class, 'index'])->name('salaries.index');
    Route::post('/salaires',                     [SalaryController::class, 'store'])->name('salaries.store');
    Route::patch('/salaires/{salary}/payer',     [SalaryController::class, 'markPaid'])->name('salaries.markPaid');
    Route::delete('/salaires/{salary}',          [SalaryController::class, 'destroy'])->name('salaries.destroy');

    // Loyalty (manager only)
    Route::get('/fidelite', [LoyaltyController::class, 'index'])->name('loyalty.index');
    Route::put('/fidelite', [LoyaltyController::class, 'update'])->name('loyalty.update');

    // Order ↔ livreur assignment (store/staff only)
    Route::post('/commandes/{order}/assigner',       [LivreurController::class, 'assignToOrder'])->name('orders.assign');
    Route::delete('/commandes/{order}/desassigner',  [LivreurController::class, 'unassign'])->name('orders.unassign');
});
