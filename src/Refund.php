<?php

namespace Triverla\LaravelMonnify;

use Triverla\LaravelMonnify\Exceptions\FailedRequestException;

abstract class Refund
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
    public function initiateRefund(string $transactionReference, string $refundReference, float $refundAmount, string $refundReason, string $customerNote, string $destinationAccountNumber, string $destinationAccountBankCode)
    {

        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/refunds/initiate-refund";

        $requestPayload = [
            "transactionReference" => $transactionReference,
            "refundReference" => $refundReference,
            "refundAmount" => $refundAmount,
            "refundReason" => $refundReason,
            "customerNote" => $customerNote,
            "destinationAccountNumber" => $destinationAccountNumber,
            "destinationAccountBankCode" => $destinationAccountBankCode

        ];

        $response = $this->monnify->withOAuth()->post($endpoint, $requestPayload);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        return $result->responseBody;
    }

    /**
     * @throws FailedRequestException
     */
    public function getRefundStatus(string $refundReference)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/refunds/$refundReference";

        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        return $result->responseBody;
    }

    /**
     * @throws FailedRequestException
     */
    public function getAllRefunds(int $pageNo = 0, int $pageSize = 10)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/refunds?pageNo=$pageNo&pageSize=$pageSize";
        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        return $result->responseBody;
    }
}
