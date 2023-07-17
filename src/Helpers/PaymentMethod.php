<?php

namespace Triverla\LaravelMonnify\Helpers;

class PaymentMethod
{
    private string $method;

    private static array $cache = [];
    private const CARD = "CARD";
    private const ACCOUNT_TRANSFER = "ACCOUNT_TRANSFER";

    private function __construct(string $method)
    {
        $this->method = $method;
    }

    public static function CARD(): PaymentMethod
    {
        if (!array_key_exists(self::CARD, self::$cache)) {
            self::$cache[self::CARD] = new PaymentMethod(self::CARD);
        }

        return self::$cache[self::CARD];
    }

    public static function ACCOUNT_TRANSFER(): PaymentMethod
    {
        if (!array_key_exists(self::ACCOUNT_TRANSFER, self::$cache)) {
            self::$cache[self::ACCOUNT_TRANSFER] = new PaymentMethod(self::ACCOUNT_TRANSFER);
        }

        return self::$cache[self::ACCOUNT_TRANSFER];
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public static function findPaymentMethod(string $method): PaymentMethod
    {
        return match ($method) {
            "card" => self::CARD(),
            "account_transfer" => self::ACCOUNT_TRANSFER()
        };

    }

    public function __toString(): string
    {
        return $this->getMethod();
    }
}
