<?php

namespace Triverla\LaravelMonnify;

use Triverla\LaravelMonnify\Exceptions\FailedRequestException;


abstract class LimitProfile
{
    /**
     * @var Monnify
     */
    private Monnify $monnify;
    /**
     * @var
     */
    private $config;

    /**
     * @param Monnify $monnify
     * @param $config
     */
    public function __construct(Monnify $monnify, $config)
    {
        $this->config = $config;
        $this->monnify = $monnify;
    }

    /**
     * @param string $limitProfileName
     * @param int $singleTransactionValue
     * @param int $dailyTransactionVolume
     * @param int $dailyTransactionValue
     * @return mixed
     * @throws FailedRequestException
     */
    public function createLimitProfile(string $limitProfileName, int $singleTransactionValue, int $dailyTransactionVolume, int $dailyTransactionValue): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/limit-profile";

        $response = $this->monnify->withOAuth()->post($endpoint, [
            "limitProfileName" => $limitProfileName,
            "singleTransactionValue" => $singleTransactionValue,
            "dailyTransactionVolume" => $dailyTransactionValue,
            "dailyTransactionValue" => $dailyTransactionValue
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param int $pageNo
     * @param int $pageSize
     * @return mixed
     * @throws FailedRequestException
     */
    public function getLimitProfiles(int $pageNo = 0, int $pageSize = 10): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/limit-profile?pageNo=$pageNo&pageSize=$pageSize";
        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $limitProfileCode
     * @param string $limitProfileName
     * @param int $singleTransactionValue
     * @param int $dailyTransactionVolume
     * @param int $dailyTransactionValue
     * @return mixed
     * @throws FailedRequestException
     */
    public function updateLimitProfile(string $limitProfileCode, string $limitProfileName, int $singleTransactionValue, int $dailyTransactionVolume, int $dailyTransactionValue): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/limit-profile/{$limitProfileCode}";

        $response = $this->monnify->withOAuth()->put($endpoint, [
            "limitProfileName" => $limitProfileName,
            "singleTransactionValue" => $singleTransactionValue,
            "dailyTransactionVolume" => $dailyTransactionValue,
            "dailyTransactionValue" => $dailyTransactionValue
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $limitProfileCode
     * @param string $accountReference
     * @param string $accountName
     * @param string $customerEmail
     * @param string|null $customerName
     * @param bool $getAllAvailableBanks
     * @param string|null $customerBvn
     * @param string|null $currencyCode
     * @return mixed
     * @throws FailedRequestException
     */
    public function reservedAccountWithLimit(string $limitProfileCode, string $accountReference, string $accountName, string $customerEmail, string $customerName = null, bool $getAllAvailableBanks = false, string $customerBvn = null, string $currencyCode = null): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/bank-transfer/reserved-accounts/limit";

        $response = $this->monnify->withOAuth()->post($endpoint, [
            "accountReference" => $accountReference,
            "accountName" => $accountName,
            "currencyCode" => $currencyCode ?? $this->config['default_currency_code'],
            "contractCode" => $this->config['contract_code'],
            "customerEmail" => $customerEmail,
            "customerName" => $customerName,
            "limitProfileCode" => $limitProfileCode
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $limitProfileCode
     * @param string $accountReference
     * @return mixed
     * @throws FailedRequestException
     */
    public function updateReservedAccountLimit(string $limitProfileCode, string $accountReference): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/bank-transfer/reserved-accounts/limit";

        $response = $this->monnify->withOAuth()->put($endpoint, [
            "accountReference" => $accountReference,
            "limitProfileCode" => $limitProfileCode
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }
}
