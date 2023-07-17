<?php

namespace Triverla\LaravelMonnify\Helpers;


class TranxList
{

    private array $transactions = [];

    public function __construct(Tranx ...$transactions)
    {
        foreach ($transactions as $transaction) {
            $this->$transactions["{$transaction->getBankAccount()}"] = $transaction->toArray();
        }
    }

    public function toArray(): array
    {
        return array_values($this->transactions);
    }

}
