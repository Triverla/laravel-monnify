<?php

namespace Triverla\LaravelMonnify;

use Triverla\LaravelMonnify\Exceptions\FailedRequestException;
use Triverla\LaravelMonnify\Helpers\CardDetails;
use Triverla\LaravelMonnify\Helpers\IncomeSplitConfig;
use Triverla\LaravelMonnify\Helpers\PaymentMethod;
use Triverla\LaravelMonnify\Helpers\PaymentMethods;

abstract class Transaction
{
    private Monnify $monnify;

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
     * @param array $queryParams
     * @return mixed
     * @throws FailedRequestException
     */
    public function getAllTransactions(array $queryParams): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/transactions/search?" . http_build_query($queryParams, '', '&amp;');

        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param float $amount
     * @param string $customerName
     * @param string $customerEmail
     * @param string $paymentReference
     * @param string $paymentDescription
     * @param string $redirectUrl
     * @param PaymentMethods $paymentMethods
     * @param IncomeSplitConfig|null $incomeSplitConfig
     * @param string|null $currencyCode
     * @return mixed
     * @throws FailedRequestException
     */
    public function initializeTransaction(float $amount, string $customerName, string $customerEmail, string $paymentReference, string $paymentDescription, string $redirectUrl, PaymentMethods $paymentMethods, IncomeSplitConfig $incomeSplitConfig = null, string $currencyCode = null, $metadata = null): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/merchant/transactions/init-transaction";

        $requestPayload = [
            "amount" => $amount,
            "customerName" => trim($customerName),
            "customerEmail" => $customerEmail,
            "paymentReference" => $paymentReference,
            "paymentDescription" => trim($paymentDescription),
            "currencyCode" => $currencyCode ?? $this->config['default_currency_code'],
            "contractCode" => $this->config['contract_code'],
            "redirectUrl" => trim($redirectUrl),
            "paymentMethods" => $paymentMethods->toArray(),
        ];
        if ($incomeSplitConfig !== null)
            $requestPayload["incomeSplitConfig"] = $incomeSplitConfig->toArray();

        if (!is_null($metadata))
            $requestPayload["metadata"] = is_object($metadata) ? $metadata : (object)$metadata;

        $response = $this->monnify->withOAuth()->post($endpoint, $requestPayload);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $paymentReference
     * @param $amountPaid
     * @param string $paidOn
     * @param string $transactionReference
     * @return bool|string
     */
    public function calculateHash(string $paymentReference, $amountPaid, string $paidOn, string $transactionReference): bool|string
    {
        $clientSecretKey = $this->config['secret_key'];
        return hash('sha512', "$clientSecretKey|$paymentReference|$amountPaid|$paidOn|$transactionReference");
    }

    /**
     * @param string $transactionReference
     * @return mixed
     * @throws FailedRequestException
     */
    public function getTransactionStatus(string $transactionReference): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v2}/transactions/$transactionReference";

        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $paymentReference
     * @return mixed
     * @throws FailedRequestException
     */
    public function getTransactionStatusByPaymentReference(string $paymentReference): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/merchant/transactions/query?paymentReference=$paymentReference";

        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $transactionReference
     * @param string $bankCode
     * @return mixed
     * @throws FailedRequestException
     */
    public function payWithBankTransfer(string $transactionReference, string $bankCode): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/merchant/bank-transfer/init-payment";

        $response = $this->monnify->withOAuth()->post($endpoint, [
            "transactionReference" => $transactionReference,
            "bankCode" => trim($bankCode),
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $transactionReference
     * @param CardDetails $cardDetails
     * @return mixed
     * @throws FailedRequestException
     */
    public function chargeCard(string $transactionReference, CardDetails $cardDetails): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/merchant/cards/charge";

        $response = $this->monnify->withOAuth()->post($endpoint, [
            "transactionReference" => $transactionReference,
            "collectionChannel" => "API_NOTIFICATION",
            "card" => $cardDetails->toArray(),
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $transactionReference
     * @param string $tokenId
     * @param string $token
     * @return mixed
     * @throws FailedRequestException
     */
    public function authorizeOTP(string $transactionReference, string $tokenId, string $token): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/merchant/cards/charge";

        $response = $this->monnify->withOAuth()->post($endpoint, [
            "transactionReference" => $transactionReference,
            "collectionChannel" => "API_NOTIFICATION",
            "tokenId" => $tokenId,
            "token" => $token
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $transactionReference
     * @param CardDetails $cardDetails
     * @return mixed
     * @throws FailedRequestException
     */
    public function authorize3DSCard(string $transactionReference, CardDetails $cardDetails): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/merchant/cards/charge";

        $response = $this->monnify->withOAuth()->post($endpoint, [
            "transactionReference" => $transactionReference,
            "collectionChannel" => "API_NOTIFICATION",
            "card" => $cardDetails->toArray(),
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }
}
