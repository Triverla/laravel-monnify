<?php

namespace Triverla\LaravelMonnify\Helpers;


class AllowedPaymentSources
{

    private array $bankAccounts = [];
    private array $accountNames = [];

    private function __construct(BankAccount ...$bankAccounts)
    {
        foreach ($bankAccounts as $account){
            $this->bankAccounts[] = $account->getBankCodeAndAccountNumber();
            $this->accountNames[] = $account->getAccountName();
        }
    }

    public function toArray(): array
    {
        return [
            "bankAccounts" => $this->bankAccounts,
            "accountNames" => $this->accountNames,
        ];
    }

}
