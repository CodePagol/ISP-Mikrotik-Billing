<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payment\WebhookPaymentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/payment/bkash/ipn', [WebhookPaymentController::class, 'handleBkashIpn']);
Route::middleware('auth:sanctum')->post('/payment/mfs/sms-receiver', [WebhookPaymentController::class, 'handleSmsReceiver']);


