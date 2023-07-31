<?php

use Illuminate\Support\Facades\Route;
use Triverla\LaravelMonnify\Http\Controllers\MonnifyWebhookController;

Route::prefix('webhooks/incoming/monnify')->group(function () {
    Route::post('collection', [MonnifyWebhookController::class, 'collection'])->name('monnify.webhook.collection');
    Route::post('transaction-completion', [MonnifyWebhookController::class, 'transactionCompletion'])->name('monnify.webhook.transaction-completion');
    Route::post('refund-completion', [MonnifyWebhookController::class, 'refundCompletion'])->name('monnify.webhook.refund-completion');
    Route::post('disbursement', [MonnifyWebhookController::class, 'disbursement'])->name('monnify.webhook.disbursement');
    Route::post('settlement', [MonnifyWebhookController::class, 'settlement'])->name('monnify.webhook.settlement');

});
