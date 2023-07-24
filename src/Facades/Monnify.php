<?php

namespace Triverla\LaravelMonnify\Facades;

use Illuminate\Support\Facades\Facade;
use Triverla\LaravelMonnify\Bank;
use Triverla\LaravelMonnify\Disbursement;
use Triverla\LaravelMonnify\Payment;
use Triverla\LaravelMonnify\Refund;
use Triverla\LaravelMonnify\ReservedAccount;
use Triverla\LaravelMonnify\Settlement;
use Triverla\LaravelMonnify\SubAccount;
use Triverla\LaravelMonnify\Transaction;
use Triverla\LaravelMonnify\Verify;

class Monnify extends Facade
{
    /**
     * @method static string computeRequestValidationHashTest(string $data)
     * @method static string computeRequestValidationHash(string $data)
     * @method static Bank bank()
     * @method static ReservedAccount reservedAccount()
     * @method static Disbursement disbursement()
     * @method static SubAccount subAccount()
     * @method static Transaction transaction()
     * @method static Payment payment()
     * @method static Verify verify()
     * @method static Refund refund()
     * @method static Settlement settlement()
     *
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-monnify';
    }
}
