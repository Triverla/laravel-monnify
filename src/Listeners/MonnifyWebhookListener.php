<?php

namespace Triverla\LaravelMonnify\Listeners;

use Illuminate\Support\Facades\Log;
use Triverla\LaravelMonnify\Events\NewIncomingWebHook;
use Triverla\LaravelMonnify\Exceptions\FailedRequestException;
use Triverla\LaravelMonnify\Facades\Monnify;

class MonnifyWebhookListener
{
    /**
     * Handle the event.
     *
     * @param NewIncomingWebHook $event
     * @return void
     */
    public function handle(NewIncomingWebHook $event)
    {
        $payload = $event->incomingWebhook;
        switch ($event->webhookType) {
            case NewIncomingWebHook::WEB_HOOK_EVENT_TRANSACTION_COMPLETION:
            {
                if ($payload->paymentStatus == 'PAID') {
                    $payloadHash = $payload->transactionHash;

                    $computedHash = Monnify::transaction()->calculateHash($payload->paymentReference, $payload->amountPaid, $payload->paidOn, $payload->transactionReference);

                    if ($payloadHash === $computedHash) {
                        try {
                            $transactionDetails = Monnify::transaction()->getTransactionStatus($payload->transactionReference);

                            if (($payload->paymentStatus == $transactionDetails->paymentStatus) &&
                                ($payload->amountPaid == $transactionDetails->amountPaid)
                            ) {
                                if ($payload->product['type'] == 'RESERVED_ACCOUNT') {
                                    $transactionReference = $payload->transactionReference;

                                    //Business Logic goes here


                                } else {
                                    //Business Logic for other types of transactions

                                }
                            }
                        } catch
                        (FailedRequestException $exception) {
                            //Handle Exception
                            logger($exception->getMessage());
                        }
                    }
                }
                break;
            }
            case
            NewIncomingWebHook::WEB_HOOK_EVENT_DISBURSEMENT :
            {
                // Do Something
                break;
            }

            case NewIncomingWebHook::WEB_HOOK_EVENT_REFUND_COMPLETION :
            {
                // Do Something
                break;
            }

            case  NewIncomingWebHook::WEB_HOOK_EVENT_SETTLEMENT :
            {
                // Do Something
                break;
            }
        }
    }
}
