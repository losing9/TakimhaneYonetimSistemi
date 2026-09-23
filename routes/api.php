<?php

use App\Http\Controllers\ApiPortalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ─── Auth (token almak için — Sanctum olmadan da çalışır) ────────────────────
Route::post('/auth/login',  [ApiPortalController::class, 'login']);

// ─── Korumalı Portal API ──────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout',        [ApiPortalController::class, 'apiLogout']);

    Route::get('/portal/tool/{serial}', [ApiPortalController::class, 'findTool']);
    Route::post('/portal/loan',         [ApiPortalController::class, 'takeLoan']);
    Route::post('/portal/verify-station',[ApiPortalController::class, 'verifyStation']);
    Route::post('/portal/return',       [ApiPortalController::class, 'returnLoan']);
    Route::get('/portal/my-loans',      [ApiPortalController::class, 'myLoans']);

    Route::get('/user', fn(Request $r) => response()->json([
        'id'   => $r->user()->id,
        'name' => $r->user()->name,
        'role' => $r->user()->role,
    ]));
});
