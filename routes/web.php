<?php

use App\Http\Controllers\ApiPortalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

// ─── Ana Sayfa → Login ────────────────────────────────────────────────────────
Route::get('/',        [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',  [AuthController::class, 'login'])->name('login.post')->middleware('throttle:login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── Personel Portalı ─────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('portal')->name('portal.')->group(function () {

    Route::get('/',         [PortalController::class, 'index'])->name('index');
    Route::get('/scan',     [PortalController::class, 'scan'])->name('scan');
    Route::get('/return',   [PortalController::class, 'returnPage'])->name('return');
    Route::get('/my-loans', [PortalController::class, 'myLoans'])->name('my-loans');
    Route::get('/profile',  [PortalController::class, 'profile'])->name('profile');
    Route::get('/slot/{slot}', [PortalController::class, 'slotDetail'])->name('slot');

    // AJAX endpoints
    Route::post('/find-tool',      [PortalController::class, 'findTool'])->name('find-tool');
    Route::post('/take-loan',      [PortalController::class, 'takeLoan'])->name('take-loan');
    Route::post('/verify-station', [PortalController::class, 'verifyStation'])->name('verify-station');
    Route::post('/find-my-loan',   [PortalController::class, 'findMyLoan'])->name('find-my-loan');
    Route::post('/confirm-return', [PortalController::class, 'confirmReturn'])->name('confirm-return');
    Route::post('/update-password',[PortalController::class, 'updatePassword'])->name('update-password');
    Route::post('/find-slot',      [PortalController::class, 'findSlot'])->name('find-slot');
});

// ─── Etiket & Rapor Exportları ────────────────────────────────────────────────
Route::middleware(['auth', 'admin-portal'])->prefix('labels')->name('labels.')->group(function () {
    Route::get('/tool/{tool}/pdf',          [LabelController::class, 'toolPdf'])->name('tool.pdf');
    Route::get('/tools/bulk',               [LabelController::class, 'toolsBulkPdf'])->name('tools.bulk');
    Route::get('/personnel/{personnel}/pdf',[LabelController::class, 'personnelPdf'])->name('personnel.pdf');
    Route::get('/personnel/bulk',           [LabelController::class, 'personnelBulkPdf'])->name('personnel.bulk');
    Route::get('/slot/{slot}/pdf',          [LabelController::class, 'slotPdf'])->name('slot.pdf');
    Route::get('/slots/all',                [LabelController::class, 'allSlotsPdf'])->name('slots.all');
    Route::get('/station/{station}/display', [LabelController::class, 'stationDisplay'])->name('stations.display');
});

// ─── İstasyon Canlı Kiosk Ekranı (Duvar / Monitör Kiosk Modu) ─────────────────
Route::get('/station/{station}/display', [LabelController::class, 'stationDisplay'])->name('station.public.display');


Route::middleware(['auth', 'admin-portal'])->prefix('export')->name('export.')->group(function () {
    Route::get('/report',        [LabelController::class, 'exportReport'])->name('report');
    Route::get('/yearly/{year}', [LabelController::class, 'exportYearly'])->name('yearly');
    Route::get('/tools/excel',   [LabelController::class, 'exportToolsExcel'])->name('tools.excel');
    Route::get('/tools/pdf',     [LabelController::class, 'exportToolsPdf'])->name('tools.pdf');
});

// ─── Yönetici (Admin) Portalı ─────────────────────────────────────────────────
Route::middleware(['auth', 'admin-portal'])->prefix('admin-portal')->name('admin-portal.')->group(function () {
    Route::get('/', [\App\Http\Controllers\AdminPortalController::class, 'index'])->name('index');
    Route::get('/add-tool', [\App\Http\Controllers\AdminPortalController::class, 'addToolPage'])->name('add-tool');
    Route::post('/add-tool', [\App\Http\Controllers\AdminPortalController::class, 'addTool'])->name('add-tool.post');
    // OCR taramasına ayrıca hız limiti: dakikada 10 istek
    Route::post('/ocr-scan', [\App\Http\Controllers\AdminPortalController::class, 'ocrScan'])->name('ocr-scan')->middleware('throttle:10,1');
    Route::post('/loans/{loan}/return', [\App\Http\Controllers\AdminPortalController::class, 'returnLoan'])->name('return-loan');
    Route::post('/loans/assign', [\App\Http\Controllers\AdminPortalController::class, 'assignLoan'])->name('assign-loan');
    Route::get('/export-daily-pdf', [\App\Http\Controllers\AdminPortalController::class, 'exportDailyPdf'])->name('export-daily-pdf');
    Route::get('/export-daily-excel', [\App\Http\Controllers\AdminPortalController::class, 'exportDailyExcel'])->name('export-daily-excel');
});

// ─── PWA ──────────────────────────────────────────────────────────────────────
Route::get('/manifest.json', fn() => response()->file(public_path('manifest.json'), ['Content-Type' => 'application/manifest+json']));
Route::get('/sw.js',         fn() => response()->file(public_path('sw.js'),         ['Content-Type' => 'application/javascript']));

// ─── İstasyon QR Etiketi ──────────────────────────────────────────────────────
Route::middleware(['auth'])->get('/stations/{station}/qr', [\App\Http\Controllers\StationController::class, 'qrLabel'])->name('station.qr');
