<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReceiptController;
use App\Livewire\Admin\AuditLogsViewer;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\DiscountManager;
use App\Livewire\Admin\MenuManager;
use App\Livewire\Admin\ReportsManager;
use App\Livewire\Admin\StaffManager;
use App\Livewire\Admin\TablesManager;
use App\Livewire\Cashier\CashierPos;
use App\Livewire\Customer\QrOrder;
use App\Livewire\Waiter\WaiterPos;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public Restaurant Website (About & Complete Menu)
Route::get('/', function () {
    $categories = \App\Models\Category::with(['menuItems' => function ($q) {
        $q->where('is_available', true)->with('variants');
    }])->where('is_active', true)->orderBy('sort_order')->get();

    $tables = \App\Models\RestaurantTable::orderBy('table_number')->get();
    $activePromotion = \App\Models\DiscountRule::getActiveRuleForDate();

    $heroVideo = \App\Models\SiteSetting::get('hero_video_path');
    $sliderImages = \App\Models\SiteSetting::get('slider_images', []);
    $reviews = \App\Models\Review::approved()->latest()->take(12)->get();
    $reviewUrl = url('/?review=1#reviews');
    $reviewQrSvg = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::size(170)->margin(1)->generate($reviewUrl));

    return view('website', compact('categories', 'tables', 'activePromotion', 'heroVideo', 'sliderImages', 'reviews', 'reviewUrl', 'reviewQrSvg'));
})->name('home');

// Customer Review Submission
Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');

// Printable Table Standee for Guest Reviews & Rating QR Code
Route::get('/print-review-qr', function () {
    $reviewUrl = url('/?review=1#reviews');
    $reviewQrSvg = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::size(280)->margin(1)->generate($reviewUrl));
    return view('admin.print_review_qr', compact('reviewUrl', 'reviewQrSvg'));
})->name('print.review.qr');

// Staff Portal Quick Switcher
Route::get('/portal', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'cashier' => redirect()->route('cashier.pos'),
            'waiter' => redirect()->route('waiter.pos'),
            'cook' => redirect()->route('kitchen.display'),
            default => redirect()->route('login'),
        };
    }

    $tables = \App\Models\RestaurantTable::orderBy('table_number')->take(4)->get();
    return view('welcome_portal', compact('tables'));
})->name('portal');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('quick.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 1. Customer QR Ordering (Public, No Login)
Route::get('/table/{qr_token}', QrOrder::class)->name('table.qr');

// Public / Protected Invoices & Print Routes
Route::get('/order/{order}/receipt', [ReceiptController::class, 'thermalReceipt'])->name('order.receipt');
Route::get('/order/{order}/kot', [ReceiptController::class, 'kotReceipt'])->name('order.kot');
Route::get('/order/{order}/pdf', [ReceiptController::class, 'downloadPdf'])->name('order.pdf');
Route::get('/order/{order}/whatsapp-share', [ReceiptController::class, 'whatsappShare'])->name('order.whatsapp');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // 2. Waiter Panel (Waiter, Cashier, Admin)
    Route::middleware(['role:waiter,cashier,admin'])->group(function () {
        Route::get('/waiter/pos', WaiterPos::class)->name('waiter.pos');
    });

    // 3. Cashier Master POS (Cashier, Admin)
    Route::middleware(['role:cashier,admin'])->group(function () {
        Route::get('/cashier/pos', CashierPos::class)->name('cashier.pos');
    });

    // 4. Kitchen Order Display / Cook Portal (Cook, Admin, Waiter, Cashier)
    Route::middleware(['role:cook,admin,cashier,waiter'])->group(function () {
        Route::get('/kitchen', \App\Livewire\Kitchen\KitchenDisplay::class)->name('kitchen.display');
        Route::get('/cook', fn() => redirect()->route('kitchen.display'))->name('cook.portal');
    });

    // 4. Admin Panel (Admin Only)
    Route::prefix('admin')->middleware(['role:admin'])->name('admin.')->group(function () {
        Route::get('/', fn() => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
        Route::get('/tables', TablesManager::class)->name('tables');
        Route::get('/menu', MenuManager::class)->name('menu');
        Route::get('/discounts', DiscountManager::class)->name('discounts');
        Route::get('/staff', StaffManager::class)->name('staff');
        Route::get('/reports', ReportsManager::class)->name('reports');
        Route::get('/audit-logs', AuditLogsViewer::class)->name('audit_logs');
        Route::get('/media', \App\Livewire\Admin\MediaManager::class)->name('media');
        Route::post('/media/video/upload', [\App\Http\Controllers\MediaController::class, 'uploadHeroVideo'])->name('media.video.upload');
        Route::post('/media/video/remove', [\App\Http\Controllers\MediaController::class, 'removeHeroVideo'])->name('media.video.remove');
        Route::get('/tables-qrs/print', [ReceiptController::class, 'printTableQrs'])->name('tables.qrs.print');
    });
});
