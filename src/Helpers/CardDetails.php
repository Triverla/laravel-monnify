<?php

namespace Triverla\LaravelMonnify\Helpers;


class CardDetails
{

    private string $cardNumber;
    private string $expiryMonth;
    private string $expiryYear;
    private string $pin;
    private string $cvv;

    public function __construct(string $cardNumber, $expiryMonth, $expiryYear, $pin, $cvv)
    {
        $this->cardNumber = trim($cardNumber);
        $this->expiryMonth = $expiryMonth;
        $this->expiryYear = $expiryYear;
        $this->pin = $pin;
        $this->cvv = $cvv;
    }

    public function toArray(): array
    {
        return [
            "number" => $this->cardNumber,
            "expiryMonth" => $this->expiryMonth,
            "expiryYear" => $this->expiryYear,
            "pin" => $this->pin,
            "cvv" => $this->cvv
        ];
    }
}
