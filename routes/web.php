<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminMaterialController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminPurchaseController;
use App\Http\Controllers\Admin\AdminSupplierController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\TicketController;
use App\Http\Controllers\Admin\AdminTicketController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SuperAdmin\SuperAdminCredentialController;
use App\Http\Controllers\SuperAdmin\SuperAdminPasswordController;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SuperAdminHeroController;
use App\Http\Controllers\SuperAdmin\SuperAdminOrderController;
use App\Http\Controllers\SuperAdmin\SuperAdminPaymentMethodController;
use App\Http\Controllers\SuperAdmin\SuperAdminProductController;
use App\Http\Controllers\SuperAdmin\SuperAdminUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Grafika South Custom Print Shop Ordering Platform
|--------------------------------------------------------------------------
*/

// 1. PUBLIC CATALOG & PRODUCTS
Route::get('/', function () {
    $heroBanners = \App\Models\HeroBanner::where('is_active', true)->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
    $products = \App\Models\Product::where('is_active', true)->with(['materials', 'options'])->latest()->get();
    $announcements = \App\Models\Announcement::where('is_active', true)->orderBy('sort_order', 'asc')->get();
    return view('welcome', compact('heroBanners', 'products', 'announcements'));
})->name('landing');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{idOrSlug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/track-order', [\App\Http\Controllers\TrackOrderController::class, 'index'])->name('orders.track');
Route::get('/support', function () {
    if (!\Illuminate\Support\Facades\Auth::check()) {
        session()->flash('warning', 'Please login or register to access Customer Support.');
        return redirect()->route('login');
    }
    $user = \Illuminate\Support\Facades\Auth::user();
    if ($user->isAdmin() || $user->isSuperAdmin()) {
        return redirect()->route('admin.tickets.index');
    }
    return redirect()->route('customer.tickets.index');
})->name('support');

// 2. PUBLIC E-COMMERCE CART
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/toggle-select', [CartController::class, 'toggleSelect'])->name('cart.toggle-select');
Route::post('/cart/select-all', [CartController::class, 'selectAll'])->name('cart.select-all');
Route::post('/cart/remove/{key}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/remove-selected', [CartController::class, 'removeSelected'])->name('cart.remove-selected');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// 3. CHECKOUT (GUARDED FOR AUTHENTICATED USERS)
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'storeOrder'])->name('checkout.store');

// ADDRESS LOOKUP API (Philippine Cascading Dropdowns: Region -> Province -> City -> Barangay)
Route::prefix('api/address')->group(function () {
    Route::get('/regions', [AddressController::class, 'regions'])->name('api.address.regions');
    Route::get('/regions/{regionCode}/provinces', [AddressController::class, 'provinces'])->name('api.address.provinces');
    Route::get('/regions/{regionCode}/provinces/{provinceCode}/cities', [AddressController::class, 'cities'])->name('api.address.cities');
    Route::get('/regions/{regionCode}/provinces/{provinceCode}/cities/{cityName}/barangays', [AddressController::class, 'barangays'])->name('api.address.barangays');
});

// 4. AUTHENTICATION & PASSWORD RESET (GUEST)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Password Reset via 6-Digit OTP — open to guests AND logged-in users (profile "Change Password").
// Kept OUTSIDE the guest group so authenticated customers are not bounced back to their dashboard.
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetOtp'])->name('password.email');
Route::get('/forgot-password/verify-otp', [ForgotPasswordController::class, 'showOtpForm'])->name('password.otp');
Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.otp.verify');
Route::get('/forgot-password/reset', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');

// Logout route (accessible globally to gracefully terminate active or stale sessions)
Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout'])->name('logout');

// Forced password change for newly-created staff accounts (first login).
Route::middleware('auth')->group(function () {
    Route::get('/set-new-password', [ForcePasswordChangeController::class, 'show'])->name('password.force.change');
    Route::post('/set-new-password', [ForcePasswordChangeController::class, 'update'])->name('password.force.change.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify')->middleware('signed');
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])->name('verification.send')->middleware('throttle:6,1');
});

// 5. ROLE-PROTECTED ROUTE GROUPS
Route::middleware('auth')->group(function () {

    // ----------------------------------------------------
    // CUSTOMER ROUTES (Role: customer, verified)
    // ----------------------------------------------------
    Route::prefix('customer')->middleware(['role:customer', 'verified'])->group(function () {
        Route::get('/dashboard', [CustomerOrderController::class, 'dashboard'])->name('customer.dashboard');
        Route::get('/orders', [CustomerOrderController::class, 'dashboard'])->name('customer.orders.index');
        Route::get('/orders/{id}', [CustomerOrderController::class, 'show'])->name('customer.orders.show');
        Route::post('/orders/{id}/cancel', [CustomerOrderController::class, 'cancelOrder'])->name('customer.orders.cancel');
        Route::get('/orders/{id}/status', [CustomerOrderController::class, 'status'])->name('customer.orders.status');
        Route::post('/orders/{id}/reupload/{orderItemId}', [CustomerOrderController::class, 'reuploadFile'])->name('customer.orders.reupload');
        Route::get('/orders/{id}/invoice', [CustomerOrderController::class, 'invoice'])->name('customer.orders.invoice');
        Route::post('/orders/{id}/chat', [CustomerOrderController::class, 'sendMessage'])->name('customer.orders.chat');

        // Product Reviews & Ratings (submitted from completed orders)
        Route::post('/reviews', [ReviewController::class, 'store'])->name('customer.reviews.store');
        Route::get('/profile', [CustomerOrderController::class, 'profile'])->name('customer.profile');
        Route::post('/profile', [CustomerOrderController::class, 'updateProfile'])->name('customer.profile.update');
        Route::post('/profile/avatar', [CustomerOrderController::class, 'updateAvatar'])->name('customer.profile.avatar.update');
        Route::delete('/profile/avatar', [CustomerOrderController::class, 'deleteAvatar'])->name('customer.profile.avatar.delete');
        Route::delete('/profile/account', [CustomerOrderController::class, 'deleteAccount'])->name('customer.profile.delete');
        Route::get('/notifications', [CustomerOrderController::class, 'notifications'])->name('customer.notifications');
        Route::post('/notifications/mark-read', [CustomerOrderController::class, 'markNotificationsRead'])->name('customer.notifications.markRead');
        Route::post('/notifications/{id}/mark-read', [CustomerOrderController::class, 'markSingleNotificationRead'])->name('customer.notifications.markSingleRead');
        Route::delete('/notifications/{id}', [CustomerOrderController::class, 'destroyNotification'])->name('customer.notifications.destroy');
        Route::delete('/notifications', [CustomerOrderController::class, 'clearAllNotifications'])->name('customer.notifications.clearAll');

        // Customer Support Ticket Routes
        Route::get('/tickets', [TicketController::class, 'index'])->name('customer.tickets.index');
        Route::get('/tickets/create', [TicketController::class, 'create'])->name('customer.tickets.create');
        Route::post('/tickets', [TicketController::class, 'store'])->name('customer.tickets.store');
        Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('customer.tickets.show');
        Route::post('/tickets/{id}/reply', [TicketController::class, 'reply'])->name('customer.tickets.reply');
    });

    // ----------------------------------------------------
    // OPERATIONAL STAFF ADMIN ROUTES (Role: admin, super_admin)
    // ----------------------------------------------------
    Route::prefix('admin')->middleware('role:admin,super_admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->middleware('permission:dashboard')->name('admin.dashboard');

        // Order operational status & design review
        Route::middleware('permission:orders')->group(function () {
            Route::get('/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
            Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
            Route::get('/orders/{id}/invoice', [AdminOrderController::class, 'invoice'])->name('admin.orders.invoice');
            Route::post('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.status');
            Route::post('/orders/{id}/payment-proof', [AdminOrderController::class, 'uploadProof'])->name('admin.orders.payment-proof');
            Route::post('/orders/files/{fileId}/review', [AdminOrderController::class, 'reviewFile'])->name('admin.orders.files.review');
            Route::post('/orders/{id}/chat', [AdminOrderController::class, 'sendMessage'])->name('admin.orders.chat');
        });

        // Material stock levels & restock
        Route::middleware('permission:materials')->group(function () {
            Route::get('/materials', [AdminMaterialController::class, 'index'])->name('admin.materials.index');
            Route::get('/materials/stock-movements', [AdminMaterialController::class, 'movements'])->name('admin.materials.movements');
            Route::post('/materials', [AdminMaterialController::class, 'store'])->name('admin.materials.store');
            Route::post('/materials/{id}/restock', [AdminMaterialController::class, 'restock'])->name('admin.materials.restock');
            Route::put('/materials/{id}', [AdminMaterialController::class, 'update'])->name('admin.materials.update');
            Route::delete('/materials/{id}', [AdminMaterialController::class, 'destroy'])->name('admin.materials.destroy');
            Route::delete('/material-categories/{category}', [AdminMaterialController::class, 'deleteCategory'])->name('admin.materials.category.delete')->where('category', '.*');
        });

        // Suppliers, catalog & purchase orders (procurement)
        Route::middleware('permission:suppliers')->group(function () {
            // Supplier directory & accounts-payable ledger
            Route::get('/suppliers', [AdminSupplierController::class, 'index'])->name('admin.suppliers.index');
            Route::get('/suppliers/export', [AdminSupplierController::class, 'export'])->name('admin.suppliers.export');
            Route::post('/suppliers', [AdminSupplierController::class, 'store'])->name('admin.suppliers.store');
            Route::get('/suppliers/{supplier}', [AdminSupplierController::class, 'show'])->name('admin.suppliers.show');
            Route::put('/suppliers/{supplier}', [AdminSupplierController::class, 'update'])->name('admin.suppliers.update');
            Route::delete('/suppliers/{supplier}', [AdminSupplierController::class, 'destroy'])->name('admin.suppliers.destroy');

            // Supplier catalog / price list
            Route::post('/suppliers/{supplier}/catalog', [AdminSupplierController::class, 'storeCatalog'])->name('admin.suppliers.catalog.store');
            Route::put('/suppliers/catalog/{item}', [AdminSupplierController::class, 'updateCatalog'])->name('admin.suppliers.catalog.update');
            Route::delete('/suppliers/catalog/{item}', [AdminSupplierController::class, 'destroyCatalog'])->name('admin.suppliers.catalog.destroy');

            // Accounts-payable payments
            Route::post('/suppliers/{supplier}/payments', [AdminSupplierController::class, 'storePayment'])->name('admin.suppliers.payments.store');
            Route::delete('/suppliers/payments/{payment}', [AdminSupplierController::class, 'destroyPayment'])->name('admin.suppliers.payments.destroy');

            // Purchase orders
            Route::get('/purchases', [AdminPurchaseController::class, 'index'])->name('admin.purchases.index');
            Route::get('/purchases/create', [AdminPurchaseController::class, 'create'])->name('admin.purchases.create');
            Route::post('/purchases', [AdminPurchaseController::class, 'store'])->name('admin.purchases.store');
            Route::get('/purchases/{purchase}', [AdminPurchaseController::class, 'show'])->name('admin.purchases.show');
            Route::post('/purchases/{purchase}/status', [AdminPurchaseController::class, 'updateStatus'])->name('admin.purchases.status');
            Route::delete('/purchases/{purchase}', [AdminPurchaseController::class, 'destroy'])->name('admin.purchases.destroy');
        });

        // Product management (add/edit)
        Route::middleware('permission:products')->group(function () {
            Route::get('/products', [AdminProductController::class, 'index'])->name('admin.products.index');
            Route::post('/products', [AdminProductController::class, 'store'])->name('admin.products.store');
            Route::put('/products/{id}', [AdminProductController::class, 'update'])->name('admin.products.update');
        });

        // Admin Support Ticket Inbox
        Route::middleware('permission:tickets')->group(function () {
            Route::get('/tickets', [AdminTicketController::class, 'index'])->name('admin.tickets.index');
            Route::get('/tickets/{id}', [AdminTicketController::class, 'show'])->name('admin.tickets.show');
            Route::post('/tickets/{id}/reply', [AdminTicketController::class, 'reply'])->name('admin.tickets.reply');
            Route::post('/tickets/{id}/status', [AdminTicketController::class, 'updateStatus'])->name('admin.tickets.status');
        });
    });

    // ----------------------------------------------------
    // SUPER ADMIN & CUSTOMIZABLE STAFF ROUTES
    // ----------------------------------------------------
    Route::prefix('super-admin')->group(function () {

        // Modules accessible to Staff Admin when permission is specifically granted
        Route::middleware('role:super_admin,admin')->group(function () {
            // Hero Slider / Banner Carousel Management
            Route::middleware('permission:hero')->group(function () {
                Route::get('/hero', [SuperAdminHeroController::class, 'edit'])->name('super-admin.hero.edit');
                Route::post('/hero', [SuperAdminHeroController::class, 'store'])->name('super-admin.hero.store');
                Route::put('/hero/{id}', [SuperAdminHeroController::class, 'update'])->name('super-admin.hero.update');
                Route::delete('/hero/{id}', [SuperAdminHeroController::class, 'destroy'])->name('super-admin.hero.destroy');
                Route::post('/hero/{id}/toggle', [SuperAdminHeroController::class, 'toggle'])->name('super-admin.hero.toggle');
                Route::post('/hero/reorder', [SuperAdminHeroController::class, 'reorder'])->name('super-admin.hero.reorder');
            });
        });

        // SELF-SERVICE SECURITY — available to Super Admin AND Staff Admins.
        // These routes only ever modify the authenticated user's own email/password,
        // and are guarded by current-password re-auth + OTP sent to the current email.
        Route::middleware('role:super_admin,admin')->group(function () {
            // Change own email (password re-auth + OTP sent to current email)
            Route::post('/credentials/send-otp', [SuperAdminCredentialController::class, 'sendOtp'])->middleware('throttle:5,1')->name('super-admin.credentials.send-otp');
            Route::match(['post', 'put'], '/credentials/update', [SuperAdminCredentialController::class, 'update'])->middleware('throttle:5,1')->name('super-admin.credentials.update');
            Route::post('/credentials/cancel', [SuperAdminCredentialController::class, 'cancel'])->name('super-admin.credentials.cancel');

            // Change own password (current-password re-auth + OTP sent to current email)
            Route::post('/password/send-otp', [SuperAdminPasswordController::class, 'sendOtp'])->middleware('throttle:5,1')->name('super-admin.password.send-otp');
            Route::match(['post', 'put'], '/password/update', [SuperAdminPasswordController::class, 'update'])->middleware('throttle:5,1')->name('super-admin.password.update');
            Route::post('/password/cancel', [SuperAdminPasswordController::class, 'cancel'])->name('super-admin.password.cancel');
        });

        // STRICTLY SUPER ADMIN FULL CONTROL ROUTES (Role: super_admin)
        Route::middleware('role:super_admin')->group(function () {
            Route::get('/dashboard', [SuperAdminDashboardController::class, 'dashboard'])->name('super-admin.dashboard');
            Route::get('/analytics/data', [SuperAdminDashboardController::class, 'analyticsData'])->name('super-admin.analytics.data');

            // Users & Admins CRUD
            Route::get('/users', [SuperAdminUserController::class, 'index'])->name('super-admin.users.index');
            Route::post('/users/admin/store', [SuperAdminUserController::class, 'storeAdmin'])->name('super-admin.users.admin.store');
            Route::put('/users/{id}', [SuperAdminUserController::class, 'update'])->name('super-admin.users.update');
            Route::delete('/users/{id}', [SuperAdminUserController::class, 'destroy'])->name('super-admin.users.destroy');
            Route::post('/users/{id}/toggle-status', [SuperAdminUserController::class, 'toggleStatus'])->name('super-admin.users.toggle-status');

            // Products, Options & BOM Builder CRUD
            Route::get('/products', [SuperAdminProductController::class, 'index'])->name('super-admin.products.index');
            Route::get('/products/create', [SuperAdminProductController::class, 'create'])->name('super-admin.products.create');
            Route::post('/products/quick-create', [SuperAdminProductController::class, 'quickCreate'])->name('super-admin.products.quick-create');
            Route::post('/products', [SuperAdminProductController::class, 'store'])->name('super-admin.products.store');
            Route::get('/products/{id}/edit', [SuperAdminProductController::class, 'edit'])->name('super-admin.products.edit');
            Route::put('/products/{id}', [SuperAdminProductController::class, 'update'])->name('super-admin.products.update');
            Route::delete('/products/{id}', [SuperAdminProductController::class, 'destroy'])->name('super-admin.products.destroy');
            Route::post('/products/{id}/options', [SuperAdminProductController::class, 'saveOption'])->name('super-admin.products.options.save');
            Route::delete('/products/options/{optionId}', [SuperAdminProductController::class, 'deleteOption'])->name('super-admin.products.options.delete');
            Route::post('/products/{id}/bom', [SuperAdminProductController::class, 'updateBom'])->name('super-admin.products.bom.update');
            Route::put('/products/options/{optionId}/bom', [SuperAdminProductController::class, 'updateOptionBom'])->name('super-admin.products.options.bom.update');
            Route::post('/products/{id}/master-save', [SuperAdminProductController::class, 'masterSave'])->name('super-admin.products.master-save');
            Route::post('/products/{id}/toggle-publish', [SuperAdminProductController::class, 'togglePublish'])->name('super-admin.products.toggle-publish');
            Route::post('/products/{id}/gallery', [SuperAdminProductController::class, 'uploadGalleryImages'])->name('super-admin.products.gallery.upload');
            Route::delete('/products/gallery/{imageId}', [SuperAdminProductController::class, 'deleteGalleryImage'])->name('super-admin.products.gallery.delete');
            Route::post('/products/gallery/{imageId}/set-primary', [SuperAdminProductController::class, 'setPrimaryImage'])->name('super-admin.products.gallery.set-primary');
            Route::post('/products/{id}/videos', [SuperAdminProductController::class, 'uploadProductVideos'])->name('super-admin.products.videos.upload');
            Route::delete('/products/videos/{videoId}', [SuperAdminProductController::class, 'deleteProductVideo'])->name('super-admin.products.videos.delete');
            Route::post('/products/videos/{videoId}/set-primary', [SuperAdminProductController::class, 'setPrimaryVideo'])->name('super-admin.products.videos.set-primary');
            Route::post('/products/{id}/media-reorder', [SuperAdminProductController::class, 'reorderProductMedia'])->name('super-admin.products.media-reorder');

            // Full Order Overrides, Final Approvals, Refunds & Cancellations
            Route::get('/orders', [SuperAdminOrderController::class, 'index'])->name('super-admin.orders.index');
            Route::post('/orders/files/{fileId}/final-approve', [SuperAdminOrderController::class, 'finalApproveFile'])->name('super-admin.orders.files.final-approve');
            Route::post('/orders/{id}/refund', [SuperAdminOrderController::class, 'processRefund'])->name('super-admin.orders.refund');
            Route::delete('/orders/{id}', [SuperAdminOrderController::class, 'destroy'])->name('super-admin.orders.destroy');

            // Payment Methods Management (Super Admin ONLY)
            Route::get('/payment-methods', [SuperAdminPaymentMethodController::class, 'index'])->name('super-admin.payment-methods.index');
            Route::post('/payment-methods', [SuperAdminPaymentMethodController::class, 'store'])->name('super-admin.payment-methods.store');
            Route::put('/payment-methods/{id}', [SuperAdminPaymentMethodController::class, 'update'])->name('super-admin.payment-methods.update');
            Route::post('/payment-methods/{id}/toggle', [SuperAdminPaymentMethodController::class, 'toggleStatus'])->name('super-admin.payment-methods.toggle');
            Route::delete('/payment-methods/{id}', [SuperAdminPaymentMethodController::class, 'destroy'])->name('super-admin.payment-methods.destroy');
        });
    });
});
