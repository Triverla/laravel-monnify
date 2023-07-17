<?php

namespace Triverla\LaravelMonnify\Helpers;


/**
 *
 */
class Tranx
{
    private float $amount;
    private string $reference;
    private string $narration;

    private BankAccount $bankAccount;

    private string $currencyCode;
    private $subAccountCode;
    private $feePercentage;
    private $splitPercentage;
    private $feeBearer;

    /**
     * @param float $amount
     * @param string $reference
     * @param string $narration
     * @param BankAccount $bankAccount
     * @param string $currencyCode
     */
    public function __construct(float $amount, string $reference, string $narration, BankAccount $bankAccount, string $currencyCode)
    {
        $this->amount = $amount;
        $this->reference = trim($reference);
        $this->narration = trim($narration);
        $this->bankAccount = $bankAccount;
        $this->currencyCode = $currencyCode;
    }

    /**
     * @return BankAccount
     */
    public function getBankAccount(): BankAccount
    {
        return $this->bankAccount;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getNarration(): string
    {
        return $this->narration;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            "subAccountCode" => $this->subAccountCode,
            "feePercentage" => $this->feePercentage,
            "splitPercentage" => $this->splitPercentage,
            "feeBearer" => $this->feeBearer
        ];
    }
}
