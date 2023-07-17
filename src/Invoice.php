<?php

namespace Triverla\LaravelMonnify;

use Triverla\LaravelMonnify\Exceptions\FailedRequestException;
use Triverla\LaravelMonnify\Helpers\IncomeSplitConfig;
use Triverla\LaravelMonnify\Helpers\PaymentMethods;

abstract class Invoice
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
     * @throws FailedRequestException
     */
    public function getAllInvoices()
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/invoice/all";
        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        return $result->responseBody;
    }

    /**
     * @param float $amount
     * @param $expiryDateTime
     * @param string $customerName
     * @param string $customerEmail
     * @param string $invoiceReference
     * @param string $invoiceDescription
     * @param string $redirectUrl
     * @param PaymentMethods $paymentMethods
     * @param IncomeSplitConfig|null $incomeSplitConfig
     * @param string|null $currencyCode
     * @return mixed
     * @throws FailedRequestException
     */
    public function createAnInvoice(float $amount, $expiryDateTime, string $customerName, string $customerEmail, string $invoiceReference, string $invoiceDescription, string $redirectUrl, PaymentMethods $paymentMethods, IncomeSplitConfig $incomeSplitConfig = null, string $currencyCode = null): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/invoice/create";

        $requestPayload = [
            "amount" => $amount,
            "expiryDate" => trim($expiryDateTime),
            "customerName" => trim($customerName),
            "customerEmail" => $customerEmail,
            "invoiceReference" => $invoiceReference,
            "description" => trim($invoiceDescription),
            "currencyCode" => $currencyCode ?? $this->config['default_currency_code'],
            "contractCode" => $this->config['contract_code'],
            "redirectUrl" => trim($redirectUrl),
            "paymentMethods" => $paymentMethods->toArray(),
        ];
        if (!is_null($incomeSplitConfig)) {
            $requestPayload["incomeSplitConfig"] = $incomeSplitConfig->toArray();
        }

        $response = $this->monnify->withOAuth()->post($endpoint, $requestPayload);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        return $result->responseBody;
    }

    /**
     * @param string $invoiceReference
     * @return mixed
     * @throws FailedRequestException
     */
    public function viewInvoiceDetails(string $invoiceReference): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/invoice/$invoiceReference/details";

        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        return $result->responseBody;
    }

    /**
     * @param string $invoiceReference
     * @return mixed
     * @throws FailedRequestException
     */
    public function cancelInvoice(string $invoiceReference): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/invoice/$invoiceReference/cancel";

        $response = $this->monnify->withOAuth()->delete($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        return $result->responseBody;
    }

    /**
     * @param string $accountName
     * @param string $customerName
     * @param string $customerEmail
     * @param string $accountReference
     * @param string|null $currencyCode
     * @return mixed
     * @throws FailedRequestException
     */
    public function reservedAccountInvoicing(string $accountName, string $customerName, string $customerEmail, string $accountReference, string $currencyCode = null): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/bank-transfer/reserved-accounts";

        $response = $this->monnify->withOAuth()->post($endpoint, [
            "contractCode" => $this->config['contract_code'],
            "accountName" => trim($accountName),
            "currencyCode" => $currencyCode ?? $this->config['default_currency_code'],
            "accountReference" => $accountReference,
            "customerEmail" => $customerEmail,
            "customerName" => trim($customerName),
            "reservedAccountType" => "INVOICE",
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        return $result->responseBody;
    }


    /**
     * @param float $amount
     * @param $expiryDateTime
     * @param string $customerName
     * @param string $customerEmail
     * @param string $invoiceReference
     * @param string $accountReference
     * @param string $invoiceDescription
     * @param IncomeSplitConfig|null $incomeSplitConfig
     * @param string|null $currencyCode
     * @return mixed
     * @throws FailedRequestException
     */
    public function attachReservedAccountToInvoice(float $amount, $expiryDateTime, string $customerName, string $customerEmail, string $invoiceReference, string $accountReference, string $invoiceDescription, IncomeSplitConfig $incomeSplitConfig = null, string $currencyCode = null): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/invoice/create";

        $requestPayload = [
            "amount" => $amount,
            "expiryDate" => trim($expiryDateTime),
            "customerName" => trim($customerName),
            "customerEmail" => $customerEmail,
            "invoiceReference" => $invoiceReference,
            "accountReference" => $accountReference,
            "description" => trim($invoiceDescription),
            "currencyCode" => $currencyCode ?? $this->config['default_currency_code'],
            "contractCode" => $this->config['contract_code'],
        ];
        if (!is_null($incomeSplitConfig)) {
            $requestPayload["incomeSplitConfig"] = $incomeSplitConfig->toArray();
        }

        $response = $this->monnify->withOAuth()->post($endpoint, $requestPayload);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        return $result->responseBody;
    }
}
