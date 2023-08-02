<?php

namespace Triverla\LaravelMonnify;


use Triverla\LaravelMonnify\Exceptions\FailedRequestException;

abstract class SubAccount
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
     * @param string $bankCode
     * @param string $accountNumber
     * @param string $email
     * @param string|null $currencyCode
     * @param string|null $splitPercentage
     * @return mixed
     * @throws FailedRequestException
     */
    public function createSubAccount(string $bankCode, string $accountNumber, string $email, string $currencyCode = null, string $splitPercentage = null): mixed
    {
        $currencyCode = $currencyCode ?? $this->config['default_currency_code'];
        $splitPercentage = $splitPercentage ?? $this->config['default_split_percentage'];

        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/sub-accounts";
        $response = $this->monnify->withOAuth()->post($endpoint, [[
            'currencyCode' => $currencyCode,
            'bankCode' => $bankCode,
            'accountNumber' => $accountNumber,
            'email' => $email,
            'defaultSplitPercentage' => $splitPercentage,
        ],]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param array $accounts
     * @return mixed
     * @throws FailedRequestException
     */
    public function createSubAccounts(array $accounts): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/sub-accounts";
        $response = $this->monnify->withOAuth()->post($endpoint, $accounts);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }


    /**
     * @return mixed
     * @throws FailedRequestException
     */
    public function getSubAccounts(): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/sub-accounts";
        $response = $this->monnify->withOAuth()->get($endpoint);

        $result = $response->json();
        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $subAccountCode
     * @return mixed
     * @throws FailedRequestException
     */
    public function deleteSubAccount(string $subAccountCode): mixed
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/sub-accounts/$subAccountCode";
        $response = $this->monnify->withOAuth()->delete($endpoint);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

    /**
     * @param string $subAccountCode
     * @param string $bankCode
     * @param string $accountNumber
     * @param string $email
     * @param string|null $currencyCode
     * @param string|null $splitPercentage
     * @return mixed
     * @throws FailedRequestException
     */
    public function updateSubAccount(string $subAccountCode, string $bankCode, string $accountNumber, string $email, string $currencyCode = null, string $splitPercentage = null): mixed
    {
        $currencyCode = $currencyCode ?? $this->config['default_currency_code'];
        $splitPercentage = $splitPercentage ?? $this->config['default_split_percentage'];

        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}/sub-accounts";

        $response = $this->monnify->withOAuth()->put($endpoint, [
            'subAccountCode' => $subAccountCode,
            'currencyCode' => $currencyCode,
            'bankCode' => $bankCode,
            'accountNumber' => $accountNumber,
            'email' => $email,
            'defaultSplitPercentage' => $splitPercentage,
        ]);

        $result = $response->object();
        if ($response->failed()) {
            throw new FailedRequestException($result->responseMessage ?? "{$result->error} - {$result->error_description}");
        }

        return $result->responseBody;
    }

}
