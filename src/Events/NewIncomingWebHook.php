<?php

namespace Triverla\LaravelMonnify\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Triverla\LaravelMonnify\Models\IncomingWebHook;

class NewIncomingWebHook
{
    use Dispatchable, SerializesModels;

    public const WEB_HOOK_LEGACY = "legacy";
    public const WEB_HOOK_EVENT_TRANSACTION_COMPLETION = "transaction_completion";
    public const WEB_HOOK_EVENT_REFUND_COMPLETION = "refund_completion";
    public const WEB_HOOK_EVENT_DISBURSEMENT = "disbursement";
    public const WEB_HOOK_EVENT_SETTLEMENT = "settlement";

    public bool $isValidTransactionHash;
    public string $webhookType;
    public IncomingWebHook $incomingWebhook;

    public function __construct(IncomingWebHook $incomingWebHook, bool $isValidTransactionHash, string $webhookType = self::WEB_HOOK_LEGACY)
    {
        $this->incomingWebhook = $incomingWebHook;
        $this->isValidTransactionHash = $isValidTransactionHash;
        $this->webhookType = $webhookType;
    }
}
