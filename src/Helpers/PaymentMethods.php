<?php

namespace Triverla\LaravelMonnify\Helpers;

class PaymentMethods
{
    private array $paymentMethods = [];

    public function __construct(PaymentMethod ...$paymentMethods)
    {
        foreach ($paymentMethods as $method) {
            $this->paymentMethods[$method->getMethod()] = $method->getMethod();
        }
    }

    public function toArray(): array
    {
        return array_values($this->paymentMethods);
    }
}
