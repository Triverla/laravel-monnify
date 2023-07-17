<?php

namespace Triverla\LaravelMonnify;

use Triverla\LaravelMonnify\Exceptions\FailedRequestException;

abstract class Verify
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
    public function bvn(string $bvnNo, string $accountName, string $dateOfBirth, string $mobileNo)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/vas/bvn-details-match";

        $response = $this->monnify->withOAuth()->post($endpoint, [
            "bvn" => $bvnNo,
            "name" => trim($accountName),
            "dateOfBirth" => $dateOfBirth,
            "mobileNo" => $mobileNo
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        return $result->responseBody;
    }

    /**
     * @throws FailedRequestException
     */
    public function bvnAccountMatch(string $bvnNo, string $accountNumber, string $bankCode)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/vas/bvn-details-match";

        $response = $this->monnify->withOAuth()->post($endpoint, [
            "bvn" => $bvnNo,
            "accountNumber" => $accountNumber,
            "bankCode" => $bankCode
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        return $result->responseBody;
    }

    /**
     * @param string $accountNumber
     * @param string $bankCode
     * @return mixed
     * @throws FailedRequestException
     */
    public function validateBankAccount(string $accountNumber, string $bankCode): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/disbursements/account/validate?accountNumber={$accountNumber}&bankCode={$bankCode}";
        $response = $this->monnify->withBasicAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        return $result->responseBody;
    }
}
