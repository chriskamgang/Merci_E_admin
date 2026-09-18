<?php

// SECURITY: this file is intentionally NOT loaded (moved out of routes/web/, which
// routes/web.php auto-loads). These legacy gateways are unused (live: KPay + GFSolutions)
// and their public success/webhook endpoints credited wallets / marked rides paid from
// client-supplied data without provider verification. Do not re-enable without fixing that.


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\XenditController;

// Route::get('/xendit/checkout', function () {
//     return view('xendit.checkout');
// });

Route::get('/xendit',[XenditController::class, 'xendit'])->name('xendit');

Route::post('/xendit/create-invoice', [XenditController::class, 'createInvoice'])->name('xendit.create.invoice');

Route::any('/xendit-success', [XenditController::class, 'invoiceCallback'])->name('xendit.callback');

