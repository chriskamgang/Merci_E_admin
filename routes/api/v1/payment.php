<?php

/*
|--------------------------------------------------------------------------
| Payment API Routes  (prefix: api/v1)
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Payment\PaymentController;
use App\Http\Controllers\Api\V1\Payment\KPayController;
use App\Http\Controllers\Api\V1\Payment\GFSolutionsController;

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::prefix('payment')
    ->middleware(['auth:sanctum', 'throttle:120,1'])
    ->group(function () {

        // Wallet history + withdrawal list
        Route::prefix('wallet')->group(function () {
            Route::get('history', [PaymentController::class, 'walletHistory'])->middleware('throttle:500,1');
            Route::get('withdrawal-requests', [PaymentController::class, 'withDrawalRequests']);
            Route::post('transfer-money-from-wallet', [PaymentController::class, 'transferMoneyFromWallet']);
            Route::post('convert-point-to-wallet', [PaymentController::class, 'transferCreditFromPoints']);
        });

        // KPay — deposits (recharge) & withdrawals
        Route::prefix('kpay')->group(function () {
            Route::post('deposit', [KPayController::class, 'initiateDeposit']);
            Route::get('deposit/{depositId}/status', [KPayController::class, 'depositStatus']);

            Route::post('withdraw', [KPayController::class, 'initiateWithdrawal']);
            Route::get('withdraw/{withdrawalId}/status', [KPayController::class, 'withdrawalStatus']);
        });

        // GFSolutions — deposits (recharge via payment page)
        Route::prefix('gfsolutions')->group(function () {
            Route::post('deposit', [GFSolutionsController::class, 'initiateDeposit']);
            Route::get('deposit/{orderId}/status', [GFSolutionsController::class, 'depositStatus']);
        });
    });

/*
|--------------------------------------------------------------------------
| Public routes — GFSolutions callbacks & return page
|--------------------------------------------------------------------------
*/
Route::prefix('payment/gfsolutions')->group(function () {
    Route::post('callback', [GFSolutionsController::class, 'callback']);
    Route::get('return', [GFSolutionsController::class, 'returnPage']);
});
