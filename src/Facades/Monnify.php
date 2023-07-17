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
     * @method static Bank Bank()
     * @method static ReservedAccount ReservedAccount()
     * @method static Disbursement Disbursement()
     * @method static SubAccount SubAccount()
     * @method static Transaction Transaction()
     * @method static Transaction Transaction()
     * @method static Payment Payment()
     * @method static Verify Verify()
     * @method static Refund Refund()
     * @method static Settlement Settlement()
     *
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-monnify';
    }
}
