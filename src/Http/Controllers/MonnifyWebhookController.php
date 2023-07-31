<?php

namespace Triverla\LaravelMonnify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Triverla\LaravelMonnify\Events\NewIncomingWebHook;
use Triverla\LaravelMonnify\Facades\Monnify;
use Triverla\LaravelMonnify\Models\IncomingWebHook;

class MonnifyWebhookController extends Controller
{
    /**
     * @param Request $request
     * @return void
     */
    public function collection(Request $request): void
    {
        $validatedPayload = $request->validate([
            'transactionReference' => 'required',
            'paymentReference' => 'required',
            'amountPaid' => 'required',
            'totalPayable' => 'required',
            'paidOn' => 'required',
            'paymentStatus' => 'required',
            'paymentDescription' => 'required',
            'transactionHash' => 'required',
            'currency' => 'required',
            'paymentMethod' => 'required',
        ]);

        $incomingWebhook = new IncomingWebHook($request->all());
        $calculatedHash = Monnify::transaction()->calculateHash($validatedPayload['paymentReference'], $validatedPayload['amountPaid'], $validatedPayload['paidOn'], $validatedPayload['transactionReference']);

        event(new NewIncomingWebHook($incomingWebhook, $calculatedHash === $validatedPayload['transactionHash']));

    }

    /**
     * @param Request $request
     * @return void
     */
    public function transactionCompletion(Request $request): void
    {
        $request->validate([
            'eventData.transactionReference' => 'required',
            'eventData.paymentReference' => 'required',
            'eventData.amountPaid' => 'required',
            'eventData.totalPayable' => 'required',
            'eventData.paidOn' => 'required',
            'eventData.paymentStatus' => 'required',
            'eventData.paymentDescription' => 'required',
            'eventData.currency' => 'required',
            'eventData.paymentMethod' => 'required',
        ]);

        $isValidHash = false;
        $incomingWebhook = $this->init($request, $isValidHash);

        event(new NewIncomingWebHook($incomingWebhook, $isValidHash, NewIncomingWebHook::WEB_HOOK_EVENT_TRANSACTION_COMPLETION));
    }

    /**
     * @param Request $request
     * @return void
     */
    public function refundCompletion(Request $request): void
    {
        $request->validate([
            'eventData.transactionReference' => 'required',
            'eventData.paymentReference' => 'required',
            'eventData.amountPaid' => 'required',
            'eventData.totalPayable' => 'required',
            'eventData.paidOn' => 'required',
            'eventData.paymentStatus' => 'required',
            'eventData.paymentDescription' => 'required',
            'eventData.currency' => 'required',
            'eventData.paymentMethod' => 'required',
        ]);

        $isValidHash = false;
        $incomingWebhook = $this->init($request, $isValidHash);

        event(new NewIncomingWebHook($incomingWebhook, $isValidHash, NewIncomingWebHook::WEB_HOOK_EVENT_REFUND_COMPLETION));

    }

    /**
     * @param Request $request
     * @return void
     */
    public function disbursement(Request $request): void
    {

        $request->validate([
            'eventData.transactionReference' => 'required',
            'eventData.paymentReference' => 'required',
            'eventData.amountPaid' => 'required',
            'eventData.totalPayable' => 'required',
            'eventData.paidOn' => 'required',
            'eventData.paymentStatus' => 'required',
            'eventData.paymentDescription' => 'required',
            'eventData.currency' => 'required',
            'eventData.paymentMethod' => 'required',
        ]);

        $isValidHash = false;
        $incomingWebhook = $this->init($request, $isValidHash);

        event(new NewIncomingWebHook($incomingWebhook, $isValidHash, NewIncomingWebHook::WEB_HOOK_EVENT_DISBURSEMENT));

    }

    /**
     * @param Request $request
     * @return void
     */
    public function settlement(Request $request): void
    {
        $request->validate([
            'eventData.transactionReference' => 'required',
            'eventData.destinationAccountNumber' => 'required',
            'eventData.amount' => 'required',
            'eventData.reference' => 'required',
            'eventData.completedOn' => 'required',
            'eventData.status' => 'required',
            'eventData.narration' => 'required',
            'eventData.currency' => 'required',
            'eventData.destinationBankName' => 'required',
        ]);

        $isValidHash = false;
        $incomingWebhook = $this->init($request, $isValidHash);

        event(new NewIncomingWebHook($incomingWebhook, $isValidHash, NewIncomingWebHook::WEB_HOOK_EVENT_SETTLEMENT));
    }

    /**
     * @param $request
     * @param $isValidHash
     * @return IncomingWebHook
     */
    private function init($request, &$isValidHash)
    {
        $monnifySignature = $request->header('monnify-signature');

        $serializedData = json_encode($request->all());
        $payload = $request->input('eventData');

        $incomingWebhook = new IncomingWebHook($payload);
        $incomingWebhook->transactionHash = $monnifySignature;
        $incomingWebhook->serializedData = $serializedData;

        $calculatedHash = Monnify::computeRequestValidationHash($serializedData);
        $isValidHash = $calculatedHash == $monnifySignature;

        return $incomingWebhook;
    }
}
