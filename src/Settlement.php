<?php

namespace Triverla\LaravelMonnify;

use Triverla\LaravelMonnify\Exceptions\FailedRequestException;

abstract class Settlement
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
     * @param string $reference
     * @param int $page
     * @param int $size
     * @return mixed
     * @throws FailedRequestException
     */
    public function getTransactionsBySettlementReference(string $reference, int $page = 0, int $size = 20): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/transactions/find-by-settlement-reference?reference=$reference&page=$page&size=$size";
        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        return $result->responseBody;
    }

    /**
     * @param string $transactionReference
     * @return mixed
     * @throws FailedRequestException
     */
    public function getSettlementInformation(string $transactionReference): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/settlement-detail?transactionReference=$transactionReference";
        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}", $result->responseCode ?? 500);
        }

        return $result->responseBody;
    }
}
