<?php

/*
|--------------------------------------------------------------------------
| Payment API Routes  (prefix: api/v1)
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Payment\PaymentController;
use App\Http\Controllers\Api\V1\Payment\PawaPayController;
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

        // PawaPay — deposits (recharge)
        Route::prefix('pawapay')->group(function () {
            Route::post('deposit', [PawaPayController::class, 'initiateDeposit']);
            Route::get('deposit/{depositId}/status', [PawaPayController::class, 'depositStatus']);

            // Payouts (driver withdrawal)
            Route::post('payout', [PawaPayController::class, 'initiatePayout']);
            Route::get('payout/{payoutId}/status', [PawaPayController::class, 'payoutStatus']);
        });

        // GFSolutions — deposits (recharge via payment page)
        Route::prefix('gfsolutions')->group(function () {
            Route::post('deposit', [GFSolutionsController::class, 'initiateDeposit']);
            Route::get('deposit/{orderId}/status', [GFSolutionsController::class, 'depositStatus']);
        });
    });

/*
|--------------------------------------------------------------------------
| Public routes — PawaPay callbacks (called by PawaPay servers)
|--------------------------------------------------------------------------
*/
Route::prefix('payment/pawapay')->group(function () {
    Route::post('callback/deposit', [PawaPayController::class, 'depositCallback']);
    Route::post('callback/payout', [PawaPayController::class, 'payoutCallback']);
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
