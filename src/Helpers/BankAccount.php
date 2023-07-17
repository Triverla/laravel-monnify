<?php

namespace Triverla\LaravelMonnify\Helpers;



use Exception;

class BankAccount
{

    private string $accountNumber;
    private string $bankCode;
    private string $accountName;

    /**
     * @throws Exception
     */
    public function __construct(string $accountNumber, string $bankCode, string $accountName = '')
    {
        $accountNumber = trim($accountNumber);
        $bankCode = trim($bankCode);

        if (empty($accountNumber))
            throw new Exception('Account Number can\'t be empty');
        else if (preg_match('#\D#', $accountNumber))
            throw new Exception('Account Number must be numeric');
        else if (strlen("$accountNumber") !== 10)
            throw new Exception('Account Number must be exactly 10 digits');

        if (empty($bankCode))
            throw new Exception('Bank Code can\'t be empty');
        else if (preg_match('#\D#', $bankCode))
            throw new Exception('Bank Code must be numeric');
        else if (strlen("$bankCode") !== 3)
            throw new Exception('Bank Code must be exactly 3 digits');

        $this->accountName = trim($accountName);
        $this->accountNumber = trim($accountNumber);
        $this->bankCode = trim($bankCode);
    }


    public function getBankCodeAndAccountNumber(): array
    {
        return [
            "accountNumber" => $this->accountNumber,
            "bankCode" => $this->bankCode,
        ];
    }

    public function getAccountName(): string
    {
        return $this->accountName;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }


    public function getBankCode(): string
    {
        return $this->bankCode;
    }

    public function __toString(): string
    {
        return "{$this->getAccountNumber()}-{$this->getBankCode()}-{$this->getAccountName()}";
    }


}
