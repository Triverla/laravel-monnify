<?php

namespace Triverla\LaravelMonnify;


use Exception;
use Triverla\LaravelMonnify\Exceptions\FailedRequestException;
use Triverla\LaravelMonnify\Helpers\AllowedPaymentSources;
use Triverla\LaravelMonnify\Helpers\IncomeSplitConfig;

abstract class ReservedAccount
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
     * @param string $accountReference
     * @param string $accountName
     * @param string $customerEmail
     * @param string|null $customerName
     * @param bool $getAllAvailableBanks
     * @param string|null $customerBvn
     * @param string|null $currencyCode
     * @param bool $restrictPaymentSource
     * @param AllowedPaymentSources|null $allowedPaymentSources
     * @param IncomeSplitConfig|null $incomeSplitConfig
     * @return mixed
     * @throws FailedRequestException
     */
    public function reserveAccount(string $accountReference, string $accountName, string $customerEmail, string $customerName = null, bool $getAllAvailableBanks = false, string $customerBvn = null, string $currencyCode = null, bool $restrictPaymentSource = false, AllowedPaymentSources $allowedPaymentSources = null, IncomeSplitConfig $incomeSplitConfig = null): mixed
    {
        if ($restrictPaymentSource && is_null($allowedPaymentSources))
            throw new Exception("Allowed Payment Sources can't be null if payment source is restricted");

        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v2}/bank-transfer/reserved-accounts";
        $requestPayload = [
            "accountReference" => $accountReference,
            "accountName" => $accountName,
            "currencyCode" => $currencyCode ?? $this->config['default_currency_code'],
            "contractCode" => $this->config['contract_code'],
            "customerEmail" => $customerEmail,
            "restrictPaymentSource" => $restrictPaymentSource,
            "getAllAvailableBanks" => $getAllAvailableBanks
        ];

        if ((!is_null($customerName)) && (!empty(trim($customerName))))
            $requestPayload['customerName'] = $customerName;

        if ((!is_null($customerBvn)) && (!empty(trim($customerBvn))))
            $requestPayload['customerBvn'] = $customerBvn;

        if (!is_null($allowedPaymentSources))
            $requestPayload['allowedPaymentSources'] = $allowedPaymentSources->toArray();

        if (!is_null($incomeSplitConfig))
            $requestPayload['incomeSplitConfig'] = $incomeSplitConfig->toArray();

        $response = $this->monnify->withOAuth()->post($endpoint, $requestPayload);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $accountReference
     * @param string $accountName
     * @param string $customerEmail
     * @param string|null $customerName
     * @param array $preferredBanksCodes
     * @param string|null $customerBvn
     * @param string|null $currencyCode
     * @param bool $restrictPaymentSource
     * @param AllowedPaymentSources|null $allowedPaymentSources
     * @param IncomeSplitConfig|null $incomeSplitConfig
     * @return mixed
     * @throws FailedRequestException
     */
    public function reserveAccountWithBankCodes(string $accountReference, string $accountName, string $customerEmail, string $customerName = null, array $preferredBanksCodes = ["035"], string $customerBvn = null, string $currencyCode = null, bool $restrictPaymentSource = false, AllowedPaymentSources $allowedPaymentSources = null, IncomeSplitConfig $incomeSplitConfig = null): mixed
    {
        if ($restrictPaymentSource && is_null($allowedPaymentSources))
            throw new Exception("Allowed Payment Sources can't be null if payment source is restricted");

        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/bank-transfer/reserved-accounts";
        $requestPayload = [
            "accountReference" => $accountReference,
            "accountName" => $accountName,
            "currencyCode" => $currencyCode ?? $this->config['default_currency_code'],
            "contractCode" => $this->config['contract_code'],
            "customerEmail" => $customerEmail,
            "restrictPaymentSource" => $restrictPaymentSource,
            "getAllAvailableBanks" => false,
            "preferredBanks" => $preferredBanksCodes
        ];

        if ((!is_null($customerName)) && (!empty(trim($customerName))))
            $requestPayload['customerName'] = $customerName;

        if ((!is_null($customerBvn)) && (!empty(trim($customerBvn))))
            $requestPayload['customerBvn'] = $customerBvn;

        if (!is_null($allowedPaymentSources))
            $requestPayload['allowedPaymentSources'] = $allowedPaymentSources->toArray();

        if (!is_null($incomeSplitConfig))
            $requestPayload['incomeSplitConfig'] = $incomeSplitConfig->toArray();


        $response = $this->monnify->withOAuth()->post($endpoint, $requestPayload);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $accountReference
     * @return mixed
     * @throws FailedRequestException
     */
    public function getAccountDetails(string $accountReference): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v2}/bank-transfer/reserved-accounts/$accountReference";
        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $accountReference
     * @param IncomeSplitConfig $incomeSplitConfig
     * @return mixed
     * @throws FailedRequestException
     */
    public function updateSplitConfig(string $accountReference, IncomeSplitConfig $incomeSplitConfig): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/bank-transfer/reserved-accounts/update-income-split-config/$accountReference";
        $response = $this->monnify->withOAuth()->put($endpoint, $incomeSplitConfig->toArray());

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $accountReference
     * @param int $page
     * @param int $size
     * @return mixed
     * @throws FailedRequestException
     */
    public function getAllTransactions(string $accountReference, int $page = 0, int $size = 10): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/bank-transfer/reserved-accounts/transactions?accountReference=$accountReference&page=$page&size=$size";
        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->json();
        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $accountNumber
     * @return mixed
     * @throws FailedRequestException
     */
    public function deallocateAccount(string $accountNumber): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/bank-transfer/reserved-accounts/$accountNumber";
        $response = $this->monnify->withOAuth()->delete($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $accountReference
     * @param AllowedPaymentSources $allowedPaymentSources
     * @return mixed
     * @throws FailedRequestException
     */
    public function restrictSourceAccount(string $accountReference, AllowedPaymentSources $allowedPaymentSources): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/bank-transfer/reserved-accounts/update-payment-source-filter/$accountReference";
        $response = $this->monnify->withOAuth()->put($endpoint, [
            "restrictPaymentSource" => true,
            "allowedPaymentSources" => $allowedPaymentSources->toArray()
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $accountReference
     * @param array $bankCodes
     * @return mixed
     * @throws FailedRequestException
     */
    public function addLinkedAccounts(string $accountReference, array $bankCodes): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/bank-transfer/reserved-accounts/add-linked-accounts/$accountReference";
        $response = $this->monnify->withOAuth()->put($endpoint, [
            "getAllAvailableBanks" => false,
            "preferredBanks" => $bankCodes
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $accountReference
     * @param string $bvn
     * @return mixed
     * @throws FailedRequestException
     */
    public function updateCustomerBvn(string $accountReference, string $bvn): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/bank-transfer/reserved-accounts/update-customer-bvn/$accountReference";
        $response = $this->monnify->withOAuth()->put($endpoint, [
            "bvn" => $bvn,
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

}
