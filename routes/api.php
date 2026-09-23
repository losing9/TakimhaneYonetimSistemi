<?php

use App\Http\Controllers\ApiDesktopController;
use App\Http\Controllers\ApiPortalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ─── Auth (token almak için — Sanctum olmadan da çalışır) ────────────────────
Route::post('/auth/login',         [ApiPortalController::class, 'login']);
Route::post('/desktop/auth/login', [ApiDesktopController::class, 'login']);
Route::get('/desktop/config',      [ApiDesktopController::class, 'config']);

// ─── Korumalı Portal & Desktop API ──────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [ApiPortalController::class, 'apiLogout']);

    // Mobil / Kiosk Portal
    Route::get('/portal/tool/{serial}',   [ApiPortalController::class, 'findTool']);
    Route::post('/portal/loan',           [ApiPortalController::class, 'takeLoan']);
    Route::post('/portal/verify-station', [ApiPortalController::class, 'verifyStation']);
    Route::post('/portal/return',         [ApiPortalController::class, 'returnLoan']);
    Route::get('/portal/my-loans',        [ApiPortalController::class, 'myLoans']);

    // C# Masaüstü Terminal API
    Route::get('/desktop/tools',          [ApiDesktopController::class, 'tools']);
    Route::get('/desktop/personnel',      [ApiDesktopController::class, 'personnel']);
    Route::post('/desktop/loan/checkout', [ApiDesktopController::class, 'checkout']);
    Route::post('/desktop/loan/checkin',  [ApiDesktopController::class, 'checkin']);

    Route::get('/user', fn(Request $r) => response()->json([
        'id'   => $r->user()->id,
        'name' => $r->user()->name,
        'role' => $r->user()->role,
    ]));
});
