<?php

namespace Triverla\LaravelMonnify;


use Triverla\LaravelMonnify\Exceptions\FailedRequestException;
use Triverla\LaravelMonnify\Helpers\BankAccount;

abstract class Bank
{
    private Monnify $monnify;
    private $config;

    public function __construct(Monnify $monnify, $config)
    {
        $this->config = $config;
        $this->monnify = $monnify;
    }

    /**
     * @throws FailedRequestException
     */
    public function getBanks()
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/banks";
        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if (!$response->successful())
            throw new FailedRequestException($result->responseMessage ?? "Path '{$result->path}' {$result->error}", $result->responseCode ?? $result->status);

        return $result->responseBody;
    }

    /**
     * @throws FailedRequestException
     */
    public function getBanksWithUSSDShortCode()
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/sdk/transactions/banks";

        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        return $result->responseBody;
    }

    /**
     * @param BankAccount $bankAccount
     * @return mixed
     * @throws FailedRequestException
     */
    public function validateBankAccount(BankAccount $bankAccount): mixed
    {

        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/disbursements/account/validate?accountNumber={$bankAccount->getAccountNumber()}&bankCode={$bankAccount->getBankCode()}";
        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        return $result->responseBody;
    }
}
