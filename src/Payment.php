<?php

namespace Triverla\LaravelMonnify;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Triverla\LaravelMonnify\Exceptions\FailedRequestException;
use Triverla\LaravelMonnify\Helpers\PaymentMethod;
use Triverla\LaravelMonnify\Helpers\PaymentMethods;

abstract class Payment
{
    private Monnify $monnify;
    private $config;
    private $url;

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


    /**
     * @param $data
     * @return $this
     * @throws FailedRequestException
     */
    public function makePaymentRequest($data = null): static
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/merchant/transactions/init-transaction";
        $paymentMethods = new PaymentMethods(PaymentMethod::CARD(), PaymentMethod::ACCOUNT_TRANSFER());

        if(is_null($data)){
            $data = [
                "amount" => request()->amount,
                "customerName" => trim(request()->customer_name),
                "customerEmail" => request()->customer_email,
                "paymentReference" => request()->reference ?? $this->generateTransactionReference(),
                "paymentDescription" => trim(request()->description),
                "currencyCode" => request()->currency ?? $this->config['default_currency_code'],
                "contractCode" => $this->config['contract_code'],
                "redirectUrl" => trim(request()->redirect_url) ?? $this->config['redirect_url'],
                "paymentMethods" => request()->payment_methods ?? $paymentMethods->toArray(),
            ];

            if (request()->has('income_split_config'))
                $data["incomeSplitConfig"] = request()->income_split_config;
        }

        if(!in_array('contractCode', $data)){
            $data['contractCode'] = $this->config['contract_code'];
        }

        $response = $this->monnify->withOAuth()->post($endpoint, $data);

        $result = $response->object();

        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        $this->url = $result->responseBody->checkoutUrl;

        return $this;
    }

    /**
     * @return bool|string
     * @throws \Exception
     */
    private function generatePaymentReference(): bool|string
    {
        $randomString = bin2hex(random_bytes(4));
       return 'MN' . $randomString;
    }

    /**
     * Fluent method to redirect to Monnify Payment Page
     */
    public function redirectNow(): RedirectResponse
    {
        return redirect($this->url);
    }


    /**
     * @param string|null $paymentReference
     * @return mixed
     */
    public function getPaymentData(string $paymentReference = null)
    {
        $reference = $paymentReference ?? request()->query('paymentReference');

        return \Triverla\LaravelMonnify\Facades\Monnify::Transaction()->getTransactionStatusByPaymentReference($reference);
    }
}
