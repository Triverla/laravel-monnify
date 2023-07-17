<?php

namespace Triverla\LaravelMonnify;

use Triverla\LaravelMonnify\Exceptions\FailedRequestException;

abstract class Payment
{
    private Monnify $monnify;
    private $config;

    public function __construct(Monnify $monnify, $config)
    {
        $this->config = $config;
        $this->monnify = $monnify;
    }

    /**
     * @param string $cardToken
     * @param float $amount
     * @param string $customerName
     * @param string $customerEmail
     * @param string $paymentReference
     * @param string $paymentDescription
     * @param string|null $currencyCode
     * @return mixed
     * @throws FailedRequestException
     */
    public function chargeCardToken(string $cardToken, float $amount, string $customerName, string $customerEmail, string $paymentReference, string $paymentDescription, string $currencyCode = null): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/merchant/cards/charge-card-token";

        $requestPayload = [
            "cardToken" => $cardToken,
            "amount" => $amount,
            "customerName" => trim($customerName),
            "customerEmail" => $customerEmail,
            "paymentReference" => $paymentReference,
            "paymentDescription" => trim($paymentDescription),
            "currencyCode" => $currencyCode ?? $this->config['default_currency_code'],
            "contractCode" => $this->config['contract_code'],
            "apiKey" => $this->config['api_key']
        ];

        $response = $this->monnify->withOAuth()->post($endpoint, $requestPayload);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        return $result->responseBody;
    }
}
